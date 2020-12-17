<?php

namespace Drupal\openy_gated_content\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Always check with custom access to '/user/{uid}/edit'.
    if ($route = $collection->get('entity.user.edit_form')) {
      $route->setRequirement('_custom_access', '\\Drupal\\openy_gated_content\\EditUserAccountAccessCheck::access');
    }
  }

}
