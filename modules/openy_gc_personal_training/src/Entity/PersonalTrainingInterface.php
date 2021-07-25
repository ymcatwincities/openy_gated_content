<?php

namespace Drupal\openy_gc_personal_training\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface for defining Personal training entities.
 *
 * @ingroup openy_gc_personal_training
 */
interface PersonalTrainingInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Gets the Personal training creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Personal training.
   */
  public function getCreatedTime();

  /**
   * Sets the Personal training creation timestamp.
   *
   * @param int $timestamp
   *   The Personal training creation timestamp.
   *
   * @return \Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface
   *   The called Personal training entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the order state.
   *
   * @return \Drupal\state_machine\Plugin\Field\FieldType\StateItemInterface
   *   The order state.
   */
  public function getState();

}
