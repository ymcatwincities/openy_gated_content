<?php

namespace Drupal\openy_gc_shared_content;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines the common interface for all SharedContentSourceType classes.
 */
interface SharedContentSourceTypeInterface extends PluginInspectionInterface {

  /**
   * Get plugin id.
   */
  public function getId();

  /**
   * Get plugin title.
   */
  public function getLabel();

  /**
   * Get plugin entity machine name.
   */
  public function getEntityType();

}
