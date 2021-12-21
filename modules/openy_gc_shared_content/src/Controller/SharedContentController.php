<?php

namespace Drupal\openy_gc_shared_content\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * The SharedContentController class.
 */
class SharedContentController extends ControllerBase {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * SharedContentController constructor.
   *
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter.
   */
  public function __construct(DateFormatter $date_formatter) {
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter')
    );
  }

  /**
   * Return the data.
   *
   * @return Symfony\Component\HttpFoundation\JsonResponse
   *   Return the JSON data.
   */
  public function index(string $type) {
    if (!in_array($type, ['gc_video', 'vy_blog_post'])) {
      return new JsonResponse(['error' => ['code' => '400']], 400);
    }

    return new JsonResponse([
      'data' => $this->getData($type),
      'method' => 'GET',
      'status' => 200,
    ]);
  }

  /**
   * Get the data to be returned.
   *
   * @return array
   *   The data to be returned.
   */
  public function getData($type) {
    $node_storage = $this->entityTypeManager()->getStorage('node');

    $result = [];
    $query = $node_storage->getQuery()
      ->condition('type', $type)
      ->sort('nid', 'ASC');
    $nodes_ids = $query->execute();
    if ($nodes_ids) {
      foreach ($nodes_ids as $node_id) {
        /** @var Drupal\node\Entity\Node $node */
        $node = $node_storage->load($node_id);

        $attributes = [
          "nid" => $node->id(),
          "title" => $node->getTitle(),
          "created" => $this->dateFormatter->format(
            $node->getCreatedTime(),
            'custom',
            'c'
          ),
          "changed" => $this->dateFormatter->format(
            $node->getChangedTime(),
            'custom',
            'c'
          ),
          "status" => $node->isPublished(),
        ];

        switch ($type) {
          case "gc_video":
            $result[] = $attributes + [
              "field_gc_video_instructor" => $node->field_gc_video_instructor->value,
              "field_gc_video_media_id" => $node->field_gc_video_media->entity ?
                $node->field_gc_video_media->entity->uuid() : NULL,
              "field_gc_video_description" => $node->field_gc_video_description->value,
              "field_gc_video_duration" => $node->field_gc_video_duration->value,
              "field_gc_video_image_id" => $node->field_gc_video_image->entity ?
                $node->field_gc_video_image->entity->uuid() : NULL,
              "field_gc_video_category_id" => $node->field_gc_video_category->entity ?
                $node->field_gc_video_category->entity->uuid() : NULL,
              "field_gc_video_equipment_id" => $node->field_gc_video_equipment->entity ?
                $node->field_gc_video_equipment->entity->uuid() : NULL,
              "field_gc_video_level_id" => $node->field_gc_video_level->entity ?
                $node->field_gc_video_level->entity->uuid() : NULL,
            ];
            break;

          case "vy_blog_post":
            $result[] = $attributes + [
              "field_vy_blog_description" => $node->field_vy_blog_description->value,
              "field_vy_blog_image_id" => $node->field_vy_blog_image->entity ?
                $node->field_vy_blog_image->entity->uuid() : NULL,
            ];
            break;
        }

      }
    }
    return $result;
  }

}
