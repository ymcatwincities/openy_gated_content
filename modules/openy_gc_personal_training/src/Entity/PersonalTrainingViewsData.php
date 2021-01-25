<?php

namespace Drupal\openy_gc_personal_training\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Personal training entities.
 */
class PersonalTrainingViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
