<?php

namespace Drupal\openy_gated_content\Controller;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CategoriesResource Controller.
 */
class CategoriesController implements ContainerInjectionInterface {

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
   * Provides a list of categories uuid's that contains videos.
   */
  public function list() {
    $query = $this->database->select('node__field_gc_video_category', 'n');
    $query->leftJoin('taxonomy_term_data', 't', 't.tid = n.field_gc_video_category_target_id');
    $query->condition('t.vid', 'gc_category');
    $query->fields('t', ['uuid']);
    $query->distinct(TRUE);
    $result = $query->execute()->fetchCol();

    return new JsonResponse($result);
  }

}
