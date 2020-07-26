<?php

/**
 * @file
 * Contains log_entity.page.inc.
 *
 * Page callback for Log entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Log entity templates.
 *
 * Default template: log_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_log_entity(array &$variables) {
  // Fetch LogEntity Entity Object.
  $log_entity = $variables['elements']['#log_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
