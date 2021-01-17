<?php

namespace Drupal\openy_gc_shared_content_server;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Shared content source entities.
 *
 * @ingroup openy_gc_shared_content_server
 */
class SharedContentSourceListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Shared content source ID');
    $header['status'] = $this->t('Status');
    $header['sync_enabled'] = $this->t('Sync Enabled');
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

    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.shared_content_source.edit_form',
      ['shared_content_source' => $entity->id()]
    );
    $row['url'] = $entity->getUrl();
    return $row + parent::buildRow($entity);
  }

}
