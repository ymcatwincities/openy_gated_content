<?php

namespace Drupal\openy_gc_shared_content\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an SharedContentSourceType annotation object.
 *
 * @Annotation
 */
class SharedContentSourceType extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The plugin entity type.
   *
   * @var string
   */
  public $entity_type;

  /**
   * The plugin entity bundle.
   *
   * @var string
   */
  public $entity_bundle;

}
