<?php

/**
 * @file
 * Contains personal_training.page.inc.
 *
 * Page callback for Personal training entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Personal training templates.
 *
 * Default template: personal_training.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_personal_training(array &$variables) {
  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
