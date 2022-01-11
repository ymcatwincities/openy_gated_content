<?php

namespace Drupal\openy_gc_shared_content_server;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Defines a class to build a listing of Shared content source entities.
 *
 * @ingroup openy_gc_shared_content_server
 */
class SharedContentSourceListBuilder extends EntityListBuilder implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['isUpdated'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Shared content source ID');
    $header['status'] = $this->t('Status');
    $header['sync_enabled'] = $this->t('Sync Enabled');
    $header['api_updated'] = $this->t('API Version');
    $header['name'] = $this->t('Name');
    $header['url'] = $this->t('Url');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /**
     * @var \Drupal\openy_gc_shared_content_server\Entity\SharedContentSource $entity
     */
    $color = '#ff0000';
    $text = $this->t('Unapproved');
    if ($entity->getStatus() == 1) {
      $color = '#008000';
      $text = $this->t('Approved');
    }
    $row['id'] = $entity->id();
    $row['status']['data'] = [
      '#type' => 'inline_template',
      '#template' => '<span style="color: ' . $color . ';">{{ content }}</span>',
      '#context' => [
        'content' => $text,
      ],
    ];

    $color = '#ff0000';
    $text = $this->t('Disabled');
    if ($entity->sync_enabled->value == 1) {
      $color = '#008000';
      $text = $this->t('Enabled');
    }

    $row['sync_enabled']['data'] = [
      '#type' => 'inline_template',
      '#template' => '<span style="color: ' . $color . ';">{{ content }}</span>',
      '#context' => [
        'content' => $text,
      ],
    ];

    $row['api_updated']['data'] = [
      '#lazy_builder' => [
        get_class() . '::isUpdated',
        [$entity->getUrl(), $entity->api_updated->value],
      ],
      '#create_placeholder' => TRUE,
    ];

    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.shared_content_source.edit_form',
      ['shared_content_source' => $entity->id()]
    );
    $row['url'] = $entity->getUrl();
    return $row + parent::buildRow($entity);
  }

  /**
   * The #lazy_builder callback; replaces placeholder with messages.
   *
   * @param string $url
   *   The url to check.
   * @param bool $is_updated
   *   If the source server has already been updated to the new version.
   *
   * @return array
   *   A renderable array containing the messages.
   */
  public static function isUpdated($url, $is_updated) {
    $response = [];

    // Attempt to hit the new endpoint and get the status code.
    // Without the proper headers it will still return 200, but no content.
    $url .= '/api/virtual-y/shared-content-source/gc_video';

    $color = '#ff0000';
    $text = t('Old');
    if ($is_updated == 1) {
      $color = '#008000';
      $text = t('New');
    }
    else {
      $client = \Drupal::httpClient();
      $status = $client->get($url, ['http_errors' => FALSE])->getStatusCode();

      if ($status == '200') {
        $color = '#ffa500';
        $text = t('Updatable!');
      }
    }

    $response = [
      '#prefix' => Markup::create("<span style='color:$color;'>"),
      '#markup' => $text,
      '#suffix' => Markup::create('</span>'),
    ];

    return $response;
  }

}
