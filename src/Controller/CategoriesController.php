<?php

namespace Drupal\openy_gated_content\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\openy_gated_content\VirtualYAccessTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CategoriesResource Controller.
 */
class CategoriesController extends ControllerBase {

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
   * Categories tree.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json Object with taxonomy tree data.
   */
  public function list(): JsonResponse {
    $bundles_entity_types = [
      'gc_video' => 'node',
      'live_stream' => 'eventinstance',
      'virtual_meeting' => 'eventinstance',
      'vy_blog_post' => 'node',
    ];

    $result = [];
    foreach ($bundles_entity_types as $bundle => $type) {
      switch ($type) {
        case 'node':
          $result[$bundle] = $this->getNodeCategories($bundle);
          break;

        case 'eventinstance':
          $result[$bundle] = $this->getEventCategories($bundle);
          break;
      }
    }

    return new JsonResponse($this->buildCategoriesTree($result));
  }

  /**
   * Categories list filtered by node's bundle.
   *
   * @param string $bundle
   *   Node type for filtering results.
   *
   * @return array
   *   Array with taxonomy id's.
   */
  protected function getNodeCategories(string $bundle): array {
    $y_roles = $this->getVirtualYmcaRoles();

    $query = $this->database->select('node__field_gc_video_category', 'n');
    $query->leftJoin('taxonomy_term_data', 't', 't.tid = n.field_gc_video_category_target_id');
    $query->leftJoin('taxonomy_term_field_data', 'tf', 't.tid = tf.tid');
    $query->condition('n.bundle', $bundle);
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

    $query->fields('t', ['tid']);
    $query->distinct(TRUE);
    return $query->execute()->fetchCol();
  }

  /**
   * Categories list filtered by event instance's bundle.
   *
   * @param string $bundle
   *   Event instance type for filtering results.
   *
   * @return array
   *   Array with taxonomy id's.
   */
  protected function getEventCategories(string $bundle): array {
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
      $query->condition('es.bundle', $bundle);
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

      $query->fields('t', ['tid']);
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
    return array_filter($user->getRoles(TRUE), static function ($role) use ($pattern) {
      return strpos($role, $pattern) !== FALSE;
    });
  }

  /**
   * Builds tree of categories, having all the specified categories as leaves.
   *
   * @param array $categories_data
   *   Categories data to include to the tree.
   *
   * @return array
   *   Resulting tree, including parents of the categories.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function buildCategoriesTree(array $categories_data): array {
    $categories_tree = [];
    /** @var \Drupal\taxonomy\TermStorageInterface $storage */
    $storage = $this->entityTypeManager()->getStorage('taxonomy_term');
    foreach ($categories_data as $bundle => $categories) {
      foreach ($categories as $category) {
        $terms_tree_branch = array_reverse($storage->loadAllParents($category));
        $current_node = &$categories_tree;
        foreach ($terms_tree_branch as $term) {
          $tid = $term->id();
          $key = array_search($tid, array_column($current_node, 'tid'));
          if ($key === FALSE) {
            $current_node[] = [
              'tid' => $tid,
              'label' => $term->label(),
              'uuid' => $term->uuid(),
              'weight' => $term->getWeight(),
              'bundles' => [],
              'children' => [],
            ];
            end($current_node);
            $key = key($current_node);
          }
          if (!in_array($bundle, $current_node[$key]['bundles'])) {
            $current_node[$key]['bundles'][] = $bundle;
          }
          $current_node = &$current_node[$key]['children'];
        }
      }
    }
    return $categories_tree;
  }

}
