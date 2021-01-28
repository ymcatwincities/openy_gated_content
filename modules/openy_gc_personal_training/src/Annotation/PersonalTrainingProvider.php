<?php

namespace Drupal\openy_gc_personal_training\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a PersonalTrainingProvider annotation object.
 *
 * @Annotation
 */
class PersonalTrainingProvider extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The administrative label of the Personal Training Provider.
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
