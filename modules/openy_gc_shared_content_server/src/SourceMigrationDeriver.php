<?php

namespace Drupal\openy_gc_shared_content_server;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Component\Plugin\Derivative\DeriverInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\openy_gc_shared_content_server\Entity\SharedContentSource;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * SourceMigration Deriver Class.
 *
 * @package Drupal\openy_gc_shared_content
 */
class SourceMigrationDeriver extends DeriverBase implements DeriverInterface, ContainerDeriverInterface {

  /**
   * EntityQuery service instance.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $sharedContentStorage;

  /**
   * The currently active request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * SourceMigrationDeriver constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   EntityTypeManager service instance.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request stack.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManager $entityTypeManager, RequestStack $requestStack) {
    $this->sharedContentStorage = $entityTypeManager
      ->getStorage('shared_content_source')
      ->getQuery()
      ->condition('sync_enabled', 1);
    $this->request = $requestStack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    $ids = $this->sharedContentStorage->execute();

    if (empty($ids)) {
      return [];
    }

    $sources = SharedContentSource::loadMultiple($ids);
    $urls = $tokens = [];
    foreach ($sources as $source) {
      $urls[] = $source->getUrl();
      $tokens[$source->getUrl()] = $source->getToken();
    }

    $params = [
      'include' => implode(',', $this->getRemoteRelationshipsList($base_plugin_definition)),
      'sort[sortByDate][path]' => 'created',
      'sort[sortByDate][direction]' => 'DESC',
      'filter[status]' => 1,
      'filter[field_gc_share]' => 1,
    ];

    $jsonapi_uri = '/jsonapi/node/' . $base_plugin_definition['source']['entity_type'] . '?' . http_build_query($params);

    foreach ($urls as $url) {

      $url_long = $url . $jsonapi_uri;
      $derivative = $this->getDerivativeValues($base_plugin_definition, $url_long, $url, $tokens[$url]);
      $this->derivatives[$this->getKey($url)] = $derivative;
    }

    return $this->derivatives;
  }

  /**
   * Private method that dynamically updates migration source.
   *
   * @param array $base_plugin_definition
   *   Plugin settings.
   * @param string $url_long
   *   Url with request part.
   * @param string $url
   *   Dynamic url for every Virtual Y content source.
   * @param string $token
   *   Server check token.
   *
   * @return array
   *   Updated plugin data.
   */
  private function getDerivativeValues(array $base_plugin_definition, $url_long, $url, $token) {

    $base_plugin_definition['source']['urls'] = $url_long;
    $base_plugin_definition['source']['headers']['x-shared-referer'] = $this
      ->request
      ->getSchemeAndHttpHost() . '/';
    $base_plugin_definition['source']['headers']['authorization'] = 'Bearer ' . $token;

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
   * @param array $base_plugin_definition
   *   Migration array.
   *
   * @return array
   *   Includes array for JSON:API
   */
  private function getRemoteRelationshipsList(array $base_plugin_definition) {
    return !empty($base_plugin_definition['source']['json_includes']) ? $base_plugin_definition['source']['json_includes'] : [];
  }

  /**
   * Helper function that prepare entity key from url.
   *
   * @param string $url
   *   Url to source.
   *
   * @return mixed
   *   Machine name url for key.
   */
  private function getKey($url) {
    $url_key = str_replace('http://', '', $url);
    $url_key = str_replace('https://', '', $url_key);
    $url_key = str_replace('.', '_', $url_key);
    return $url_key;
  }

}
