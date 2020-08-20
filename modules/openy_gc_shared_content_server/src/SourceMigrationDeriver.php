<?php

namespace Drupal\openy_gc_shared_content_server;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Component\Plugin\Derivative\DeriverInterface;
use Drupal\openy_gc_shared_content_server\Entity\SharedContentSource;

/**
 * Class SourceMigrationDeriver.
 *
 * @package Drupal\openy_gc_shared_content
 */
class SourceMigrationDeriver extends DeriverBase implements DeriverInterface {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    $ids =  \Drupal::service('entity.query')
      ->get('shared_content_source')->execute();

    if (empty($ids)) {
      return [];
    }

    $sources = SharedContentSource::loadMultiple($ids);
    $urls = [];
    foreach ($sources as $source) {
      $urls[] = $source->getUrl();
    }

    $params = [
      'include' => implode(',', $this->getRemoteRelationshipsList($base_plugin_definition)),
      'sort[sortByDate][path]' => 'created',
      'sort[sortByDate][direction]' => 'DESC',
      'filter[status]' => 1,
      //@TODO add shared_content_filter once it will be at test servers.
    ];

    $jsonapi_uri = '/jsonapi/node/' . $base_plugin_definition['source']['entity_type'] . '?' . http_build_query($params);

    foreach ($urls as $url) {
      $url_long = $url . $jsonapi_uri;
      $derivative = $this->getDerivativeValues($base_plugin_definition, $url_long, $url);
      $this->derivatives[$this->getKey($url)] = $derivative;
    }

    return $this->derivatives;
  }

  /**
   * Private method that dynamically updates migration source.
   *
   * @param array $base_plugin_definition
   *   Plugin settings.
   * @param $url_long
   *   Url with request part
   * @param $url
   *   Dynamic url for every Virtual Y content source.
   *
   * @return array
   */
  private function getDerivativeValues(array $base_plugin_definition, $url_long, $url) {

    $base_plugin_definition['source']['urls'] = $url_long;

    if (!empty($base_plugin_definition['process']['field_gc_video_media'])) {
      $migration = str_replace(
        'REPLACE_ME',
        $this->getKey($url),
        $base_plugin_definition['process']['field_gc_video_media']['migration']
      );
      $base_plugin_definition['process']['field_gc_video_media']['migration'] = $migration;
      $base_plugin_definition['migration_dependencies']['required'] = [$migration];
    }

    if (!empty($base_plugin_definition['process']['field_vy_blog_image'])) {
      $migration = str_replace(
        'REPLACE_ME',
        $this->getKey($url),
        $base_plugin_definition['process']['field_vy_blog_image']['migration']
      );
      $base_plugin_definition['process']['field_vy_blog_image']['migration'] = $migration;
      $base_plugin_definition['migration_dependencies']['required'] = [$migration];
    }

    if (!empty($base_plugin_definition['process']['field_media_image'])) {
      $migration = str_replace(
        'REPLACE_ME',
        $this->getKey($url),
        $base_plugin_definition['process']['field_media_image']['migration']
      );
      $base_plugin_definition['process']['field_media_image']['migration'] = $migration;
      $base_plugin_definition['migration_dependencies']['required'] = [$migration];
    }

    if (!empty($base_plugin_definition['source']['constants']['DOMAIN'])) {
      $base_plugin_definition['source']['constants']['DOMAIN'] = $url;
    }

    if (!empty($base_plugin_definition['process']['field_gc_video_image'])) {
      $migration = str_replace(
        'REPLACE_ME',
        $this->getKey($url),
        $base_plugin_definition['process']['field_gc_video_image']['migration']
      );
      $base_plugin_definition['process']['field_gc_video_image']['migration'] = $migration;
      $base_plugin_definition['migration_dependencies']['required'][] = $migration;
    }

    if (!empty($base_plugin_definition['process']['field_gc_video_category'])) {
      $migration = str_replace(
        'REPLACE_ME',
        $this->getKey($url),
        $base_plugin_definition['process']['field_gc_video_category']['migration']
      );
      $base_plugin_definition['process']['field_gc_video_category']['migration'] = $migration;
      $base_plugin_definition['migration_dependencies']['required'][] = $migration;
    }

    if (!empty($base_plugin_definition['process']['field_gc_video_equipment'])) {
      $migration = str_replace(
        'REPLACE_ME',
        $this->getKey($url),
        $base_plugin_definition['process']['field_gc_video_equipment']['migration']
      );
      $base_plugin_definition['process']['field_gc_video_equipment']['migration'] = $migration;
      $base_plugin_definition['migration_dependencies']['required'][] = $migration;
    }

    if (!empty($base_plugin_definition['process']['field_gc_video_level'])) {
      $migration = str_replace(
        'REPLACE_ME',
        $this->getKey($url),
        $base_plugin_definition['process']['field_gc_video_level']['migration']
      );
      $base_plugin_definition['process']['field_gc_video_level']['migration'] = $migration;
      $base_plugin_definition['migration_dependencies']['required'][] = $migration;
    }

    return $base_plugin_definition;
  }

  /**
   * Helper function that checks if we need includes to jsonapi request.
   *
   * @param $base_plugin_definition
   *   Migration array.
   *
   * @return array
   *   Includes array for JSON:API
   */
  private function getRemoteRelationshipsList($base_plugin_definition) {
    return !empty($base_plugin_definition['source']['json_includes']) ? $base_plugin_definition['source']['json_includes'] : [];
  }

  /**
   * Helper function that prepare entity key from url.
   *
   * @param $url
   *   Url to source.
   *
   * @return mixed
   *   Machine name url for key.
   */
  private function getKey($url) {
    $url_key = str_replace('http://', '', $url);
    $url_key = str_replace('.', '_', $url_key);
    return $url_key;
  }

}
