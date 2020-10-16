<?php

namespace Drupal\openy_gc_log;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Defines a class to build a listing of Log entity entities.
 *
 * @ingroup openy_gc_log
 */
class LogEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Log entity ID');
    $header['email'] = $this->t('email');
    $header['event_type'] = $this->t('event_type');
    $header['payload'] = $this->t('payload');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /**
     * @var \Drupal\openy_gc_log\Entity\LogEntity $entity
     */
    $row['id'] = $entity->id();
    $row['email'] = $entity->get('email')->value;
    $row['event_type'] = $entity->get('event_type')->value;
    $row['payload'] = $entity->get('payload')->value;
    return $row + parent::buildRow($entity);
  }

}
