<?php

namespace Drupal\openy_gated_content\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\openy_gated_content\VirtualYAccessTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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
   * Categories list.
   *
   * Provides a list of categories uuid's that contains
   * videos for specific bundle.
   *
   * @param string $type
   *   Node bundle.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json Array with uuid's.
   */
  public function list(string $type) {
    $user = $this->currentUser();
    $pattern = 'virtual_y';
    // Get list of vy roles for current user.
    $y_roles = array_filter($user->getRoles(), function ($role) use ($pattern) {
      return strpos($role, $pattern) !== FALSE;
    });

    $query = $this->database->select('node__field_gc_video_category', 'n');
    $query->leftJoin('taxonomy_term_data', 't', 't.tid = n.field_gc_video_category_target_id');
    $query->leftJoin('taxonomy_term_field_data', 'tf', 't.tid = tf.tid');
    $query->condition('n.bundle', $type);
    $query->condition('t.vid', 'gc_category');
    $query->condition('tf.status', 1);

    if (!empty($y_roles) && !in_array(self::getVirtualyEditorRoles(), $y_roles)) {
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
    $result = $query->execute()->fetchCol();

    return new JsonResponse($result);
  }

}
