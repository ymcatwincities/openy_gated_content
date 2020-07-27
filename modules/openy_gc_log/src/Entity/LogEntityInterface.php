<?php

namespace Drupal\openy_gc_log\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;

/**
 * Provides an interface for defining Log entity entities.
 *
 * @ingroup openy_gc_log
 */
interface LogEntityInterface extends ContentEntityInterface {

  const EVENT_TYPE_LOGIN = 'userLoggedIn';
  const EVENT_TYPE_ENTITY_VIEW = 'entityView';

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Log entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Log entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Log entity creation timestamp.
   *
   * @param int $timestamp
   *   The Log entity creation timestamp.
   *
   * @return \Drupal\openy_gc_log\Entity\LogEntityInterface
   *   The called Log entity entity.
   */
  public function setCreatedTime($timestamp);

}
