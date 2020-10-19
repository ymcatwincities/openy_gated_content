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
    $row['id'] = $entity->id();
    $row['status'] = $entity->getStatus() == 1 ? $this->t('Approved') : $this->t('Unapproved');
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.shared_content_source.edit_form',
      ['shared_content_source' => $entity->id()]
    );
    $row['url'] = $entity->getUrl();
    return $row + parent::buildRow($entity);
  }

}
