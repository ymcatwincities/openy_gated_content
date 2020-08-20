<?php

namespace Drupal\openy_gc_shared_content\Plugin\SharedContentSourceType;

use Drupal\openy_gc_shared_content\SharedContentSourceTypeBase;

/**
 * Defines the SharedContentSourceType plugin for Virtual Y blog post.
 *
 * @SharedContentSourceType(
 *   id="vy_blog_post",
 *   label = @Translation("Virtual Y blog post"),
 *   entityType="node",
 *   entityBundle="vy_blog_post"
 * )
 */
class VirtualYBlogPost extends SharedContentSourceTypeBase {

  /**
   * {@inheritdoc}
   */
  public function getTeaserJsonApiQueryArgs() {
    return [
      'sort[sortByDate][path]' => 'created',
      'sort[sortByDate][direction]' => 'DESC',
      'filter[status]' => 1,
      'filter[field_gc_share]' => 1,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFullJsonApiQueryArgs() {
    return [
      'include' => 'field_vy_blog_image,field_vy_blog_image.field_media_image',
      'filter[status]' => 1,
      'filter[field_gc_share]' => 1,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getExcludedRelationships() {
    return [
      'node_type',
      'revision_uid',
      'uid',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIncludedRelationships() {
    return [
      'field_vy_blog_image',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function processJsonApiData(&$data) {
    unset($data['data']['attributes']['drupal_internal__nid']);
    unset($data['data']['attributes']['drupal_internal__vid']);
  }

  /**
   * {@inheritdoc}
   */
  public function entityExists($uuid) {
    $exists = $this->entityTypeManager->getStorage($this->getEntityType())
      ->getQuery()
      ->condition('type', $this->getEntityBundle())
      ->condition('uuid', $uuid)
      ->execute();

    return !empty($exists);
  }

  /**
   * {@inheritdoc}
   */
  public function formatItem($data, $teaser = TRUE) {
    if ($teaser) {
      return $data['attributes']['title'];
    }

    $content = [
      '#theme' => 'openy_gc_shared_content__vy_blog_post',
      '#title' => $data['data']['attributes']['title'],
      '#description' => $data['data']['attributes']['field_vy_blog_description']['processed'],
      '#image' => '',
    ];

    if (!empty($data['data']['relationships']['field_vy_blog_image']['data'])) {
      foreach ($data['included'] as $included) {
        if ($included['type'] == 'file--file') {
          $content['#image'] = $included['attributes']['uri']['url'];
        }
      }
    }
    return $content;
  }

}
