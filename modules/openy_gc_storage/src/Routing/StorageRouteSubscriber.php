<?php

namespace Drupal\openy_gc_storage\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class StorageRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Move recurring_events to admin UI.
    $routes_list_to_fix = [
      'entity.eventseries.canonical',
      'entity.eventseries.add_form',
      'entity.eventseries.add_page',
      'entity.eventseries.edit_form',
      'entity.eventseries.delete_form',
      'entity.eventseries.clone_form',
      'entity.eventseries.collection',
      'entity.eventinstance.canonical',
      'entity.eventinstance.edit_form',
      'entity.eventinstance.delete_form',
      'entity.eventinstance.clone_form',
      'entity.eventinstance.collection',
      'entity.eventinstance.collection',
    ];
    foreach ($routes_list_to_fix as $route_name) {
      if ($route = $collection->get($route_name)) {
        $route->setOption('_admin_route', TRUE);
      }
    }
  }

}
