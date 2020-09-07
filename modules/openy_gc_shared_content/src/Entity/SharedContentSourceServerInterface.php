<?php

namespace Drupal\openy_gc_shared_content\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Interface SharedContentSourceInterface.
 */
interface SharedContentSourceServerInterface extends ConfigEntityInterface {

  /**
   * Gets the url to the server.
   *
   * @return string
   *   The url to the server.
   */
  public function getUrl();

  /**
   * Gets the token to the server.
   *
   * @return string
   *   The access token to the server.
   */
  public function getToken();

}
