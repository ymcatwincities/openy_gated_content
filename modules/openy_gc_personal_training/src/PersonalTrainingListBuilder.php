<?php

namespace Drupal\openy_gc_personal_training;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Personal training entities.
 *
 * @ingroup openy_gc_personal_training
 */
class PersonalTrainingListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Personal training ID');
    $header['customer'] = $this->t('Customer');
    $header['instructor'] = $this->t('Instructor');
    $header['training_type'] = $this->t('Training type');
    $header['date'] = $this->t('Date');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['id'] = Link::createFromRoute(
      '#' . $entity->id(),
      'entity.personal_training.canonical',
      ['personal_training' => $entity->id()]
    );
    $row['customer'] = Link::createFromRoute(
      $entity->customer_id->entity->label(),
      'entity.user.canonical',
      ['user' => $entity->customer_id->entity->id()]
    );
    $row['instructor'] = Link::createFromRoute(
      $entity->instructor_id->entity->label(),
      'entity.user.canonical',
      ['user' => $entity->instructor_id->entity->id()]
    );
    $row['training_type'] = $entity->get('training_type')->value;
    $row['date'] = $entity->get('date')->value;

    return $row + parent::buildRow($entity);
  }

}
