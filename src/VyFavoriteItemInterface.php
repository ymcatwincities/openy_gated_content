<?php

namespace Drupal\openy_gated_content;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a vy_favorite_item entity.
 */
interface VyFavoriteItemInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * Returns the referenced entity bundle.
   *
   * @return string
   *   The referenced entity bundle.
   */
  public function getRefEntityBundle();

  /**
   * Returns the referenced entity type.
   *
   * @return string
   *   The referenced entity type.
   */
  public function getRefEntityType();

  /**
   * Returns the referenced entity ID.
   *
   * @return string
   *   The referenced entity ID.
   */
  public function getRefEntityId();

}
