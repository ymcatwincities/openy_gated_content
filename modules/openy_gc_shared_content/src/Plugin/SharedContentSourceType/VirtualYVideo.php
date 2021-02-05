<?php

namespace Drupal\openy_gc_shared_content\Plugin\SharedContentSourceType;

use Drupal\openy_gc_shared_content\SharedContentSourceTypeBase;

/**
 * Defines the SharedContentSourceType plugin for Virtual Y video.
 *
 * @SharedContentSourceType(
 *   id="gc_video",
 *   label = @Translation("Virtual Y video"),
 *   entityType="node",
 *   entityBundle="gc_video"
 * )
 */
class VirtualYVideo extends SharedContentSourceTypeBase {

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
      'include' => 'field_gc_video_media,field_gc_video_media.thumbnail,field_gc_video_level,field_gc_video_category,field_gc_video_equipment',
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
      'field_gc_video_category',
      'field_gc_video_equipment',
      'field_gc_video_level',
      'field_gc_video_media',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function processJsonApiData(&$data) {
    unset($data['data']['attributes']['drupal_internal__nid']);
    unset($data['data']['attributes']['drupal_internal__vid']);
    unset($data['data']['attributes']['field_share_count']);
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
      '#theme' => 'openy_gc_shared_content__gc_video',
      '#title' => $data['data']['attributes']['title'],
      '#description' => $data['data']['attributes']['field_gc_video_description']['processed'],
      '#field_gc_video_level' => NULL,
      '#field_gc_video_instructor' => $data['data']['attributes']['field_gc_video_instructor'],
      '#field_gc_video_category' => NULL,
      '#field_gc_video_equipment' => [],
      '#field_gc_video_media' => NULL,
    ];
    $searched_rel = [
      'field_gc_video_category',
      'field_gc_video_equipment',
      'field_gc_video_level',
      'field_gc_video_media',
    ];
    foreach ($data['data']['relationships'] as $field_name => $value) {
      if (!in_array($field_name, $searched_rel)) {
        continue;
      }
      if (isset($value['data'][0])) {
        // Can be multiple.
        $rel_data = $value['data'];
      }
      else {
        // For single value save like multiple.
        $rel_data[0] = $value['data'];
      }

      foreach ($rel_data as $seared_item) {
        foreach ($data['included'] as $item) {
          if (isset($item['type'], $seared_item['type']) && $item['type'] == $seared_item['type'] && $item['id'] == $seared_item['id']) {
            if (strpos($item['type'], 'taxonomy_term') !== FALSE) {
              $content['#' . $field_name][] = $item['attributes']['name'];
            }
            if ($item['type'] == 'media--video') {
              if ($item['attributes']['field_media_source'] == 'youtube') {
                $url = 'https://www.youtube.com/embed/' . $item['attributes']['field_media_video_id'];
              }
              elseif ($item['attributes']['field_media_source'] == 'vimeo') {
                $url = 'https://player.vimeo.com/video/' . $item['attributes']['field_media_video_id'];
              }
              $content['#' . $field_name] = [
                '#type' => 'html_tag',
                '#tag' => 'iframe',
                '#attributes' => [
                  'src' => $url,
                  'frameborder' => 0,
                  'allow' => 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture',
                  'scrolling' => FALSE,
                  'allowtransparency' => TRUE,
                  'width' => '100%',
                  'height' => '400px',
                  'class' => ['media-oembed-content'],
                ],
                '#attached' => [
                  'library' => ['media/oembed.formatter'],
                ],
              ];
            }
            break 2;
          }
        }
      }
    }

    return $content;
  }

}
