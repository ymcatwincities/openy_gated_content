<?php

namespace Drupal\openy_gc_auth\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a GCIdentityProvider annotation object.
 *
 * @Annotation
 */
class GCIdentityProvider extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The administrative label of the Identity Provider.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label = '';

  /**
   * The configuration ID related to current plugin.
   *
   * @var string
   */
  public $config;

}
