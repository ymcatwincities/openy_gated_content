<?php

namespace Drupal\openy_gc_auth\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event that is fired when a user logs out from GC.
 */
class GCUserLogoutEvent extends Event {

  const EVENT_NAME = 'gated_content_events_user_logout';

  /**
   * Constructs the object.
   */
  public function __construct() {}

}
