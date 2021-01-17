<?php

namespace Drupal\openy_gated_content\Controller;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\openy_gated_content\VirtualYAccessTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CategoriesResource Controller.
 */
class CategoriesController extends ControllerBase implements ContainerInjectionInterface {

  use VirtualYAccessTrait;

  /**
   * The current active database's master connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a CustomFormattersController object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The current active database's master connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Categories list with filters by content type/bundle.
   *
   * Provides a list of categories uuid's that contains
   * videos for specific bundle.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json Array with taxonomy uuid's.
   */
  public function list(Request $request): JsonResponse {
    $request_data = UrlHelper::parse($request->getUri());
    if (empty($request_data['query']) || !isset($request_data['query']['type'])) {
      return new JsonResponse(['message' => 'Missed "type" query param.'], 400);
    }

    if ($request_data['query']['type'] !== 'all' && !isset($request_data['query']['bundle'])) {
      return new JsonResponse(['message' => 'For specified "type" should be "bundle" query param.'], 400);
    }

    $result = [];
    switch ($request_data['query']['type']) {
      case 'all':
        $data1 = $this->getNodeCategories(['gc_video', 'vy_blog_post']);
        $data2 = $this->getEventCategories(['live_stream', 'virtual_meeting']);
        $result = array_unique(array_merge($data1, $data2));
        break;

      case 'node':
        $result = $this->getNodeCategories([$request_data['query']['bundle']]);
        break;

      case 'eventinstance':
        $result = $this->getEventCategories([$request_data['query']['bundle']]);
        break;
    }

    return new JsonResponse(array_values($result));
  }

  /**
   * Categories list filtered by node content and bundle.
   *
   * @param array $bundles
   *   Node types for filtering results.
   *
   * @return array
   *   Array with taxonomy uuid's.
   */
  protected function getNodeCategories(array $bundles): array {
    $y_roles = $this->getVirtualYmcaRoles();

    $query = $this->database->select('node__field_gc_video_category', 'n');
    $query->leftJoin('taxonomy_term_data', 't', 't.tid = n.field_gc_video_category_target_id');
    $query->leftJoin('taxonomy_term_field_data', 'tf', 't.tid = tf.tid');
    $query->condition('n.bundle', $bundles, 'IN');
    $query->condition('t.vid', 'gc_category');
    $query->condition('tf.status', 1);

    if (!empty($y_roles) && empty(array_intersect($this->getVirtualyEditorRoles(), $y_roles))) {
      $query->leftJoin('node_field_data', 'nd', 'n.entity_id = nd.nid');
      $or_group = $query->orConditionGroup();
      foreach ($y_roles as $role) {
        $or_group->condition('nd.field_vy_permission', $role, '=');
        $or_group->condition('nd.field_vy_permission', '%' . $role . ',%', 'LIKE');
        $or_group->condition('nd.field_vy_permission', '%,' . $role, 'LIKE');
      }
      $query->condition($or_group);
    }

    $query->fields('t', ['uuid']);
    $query->distinct(TRUE);
    return $query->execute()->fetchCol();
  }

  /**
   * Categories list filtered by eventinstance content and bundle.
   *
   * @param array $bundles
   *   Event instance types for filtering results.
   *
   * @return array
   *   Array with taxonomy uuid's.
   */
  protected function getEventCategories(array $bundles): array {
    $y_roles = $this->getVirtualYmcaRoles();
    $result = [];
    $event_tables = [
      'eventseries__field_ls_category' => 'eventseries_field_data',
      'eventinstance__field_ls_category' => 'eventinstance_field_data',
    ];

    foreach ($event_tables as $field_category_table => $field_data_table) {
      $query = $this->database->select($field_category_table, 'es');
      $query->leftJoin('taxonomy_term_data', 't', 't.tid = es.field_ls_category_target_id');
      $query->leftJoin('taxonomy_term_field_data', 'tf', 't.tid = tf.tid');
      $query->condition('es.bundle', $bundles, 'IN');
      $query->condition('t.vid', 'gc_category');
      $query->condition('tf.status', 1);

      if (!empty($y_roles) && empty(array_intersect($this->getVirtualyEditorRoles(), $y_roles))) {
        $query->leftJoin($field_data_table, 'esfd', 'es.entity_id = esfd.id');
        $or_group = $query->orConditionGroup();
        foreach ($y_roles as $role) {
          $or_group->condition('esfd.field_vy_permission', $role, '=');
          $or_group->condition('esfd.field_vy_permission', '%' . $role . ',%', 'LIKE');
          $or_group->condition('esfd.field_vy_permission', '%,' . $role, 'LIKE');
        }
        $query->condition($or_group);
      }

      $query->fields('t', ['uuid']);
      $query->distinct(TRUE);
      $result = array_merge($result, $query->execute()->fetchCol());
    }

    return array_unique($result);
  }

  /**
   * Get current user Virtual Y roles.
   *
   * @return array
   *   Array with taxonomy uuid's.
   */
  protected function getVirtualYmcaRoles(): array {
    $user = $this->currentUser();
    $pattern = 'virtual_y';
    // Get list of vy roles for current user.
    return array_filter($user->getRoles(), function ($role) use ($pattern) {
      return strpos($role, $pattern) !== FALSE;
    });
  }

}
