<?php

namespace Drupal\openy_gc_auth\Event;

use Drupal\user\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event that is fired when a user logs in to GC.
 */
class GCUserLoginEvent extends Event {

  const EVENT_NAME = 'gated_content_events_user_login';

  /**
   * The user account.
   *
   * @var \Drupal\user\UserInterface
   */
  public $account;

  /**
   * Optional data.
   *
   * @var array
   */
  public $extraData;

  /**
   * Constructs the object.
   *
   * @param \Drupal\user\UserInterface $account
   *   The account of the user logged in.
   * @param array $extra_data
   *   Array with optional data.
   */
  public function __construct(UserInterface $account, array $extra_data) {
    $this->account = $account;
    $this->extraData = $extra_data;
  }

}
