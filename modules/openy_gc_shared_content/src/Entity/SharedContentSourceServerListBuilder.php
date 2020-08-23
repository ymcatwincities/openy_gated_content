<?php

namespace Drupal\openy_gc_shared_content\Entity;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of SharedContentSource entities.
 */
class SharedContentSourceServerListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Source label');
    $header['id'] = $this->t('Machine name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    if ($entity->access('edit') && $entity->hasLinkTemplate('fetch-form')) {
      $operations['fetch'] = [
        'title' => $this->t('Fetch'),
        'weight' => -100,
        'url' => $this->ensureDestination($entity->toUrl('fetch-form')),
      ];
    }

    return $operations;
  }

}
