<?php

namespace Drupal\openy_gc_shared_content;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Component\Plugin\Derivative\DeriverInterface;

/**
 * Class SourceMigrationDeriver
 *
 * @package Drupal\openy_gc_shared_content
 */
class SourceMigrationDeriver extends DeriverBase implements DeriverInterface {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    //@TODO rewrite it to source entities
    $urls = [
      'http://rose.demo.openy.ci.fivejars.com',
      'http://lily.demo.openy.ci.fivejars.com',
      'http://carnation.demo.openy.ci.fivejars.com'
    ];

    $params = [
      'include' => implode(',', $this->getRemoteRelationshipsList($base_plugin_definition)),
      'sort[sortByDate][path]' => 'created',
      'sort[sortByDate][direction]' => 'DESC',
      'filter[status]' => 1,
      //@TODO add shared_content_filter once it will be at test servers.
      //'limit' => 1000
    ];

    $jsonapi_uri = '/jsonapi/node/gc_video?' . http_build_query($params);

    foreach ($urls as $url) {
      $url_long = $url . $jsonapi_uri;
      $derivative = $this->getDerivativeValues($base_plugin_definition, $url_long, $url);
      //dump($url_long);
      $this->derivatives[$this->getKey($url)] = $derivative;
    }

    return $this->derivatives;
  }

  /**
   * Private method that dynamically updates migration source.
   *
   * @param array $base_plugin_definition
   *   Plugin settings.
   * @param $url
   *   Dynamic url for every Virtual Y content source.
   *
   * @return arrayx
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

    return $base_plugin_definition;
  }

  private function getRemoteRelationshipsList($base_plugin_definition) {
    return !empty($base_plugin_definition['source']['json_includes']) ? $base_plugin_definition['source']['json_includes'] : [];
  }

  private function getKey($url) {
    $url_key = str_replace('http://', '', $url);
    $url_key = str_replace('.', '_', $url_key);
    return $url_key;
  }

}