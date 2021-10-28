<?php

namespace Drupal\openy_gc_log\EventSubscriber;

use Drupal\openy_gc_auth\Event\GCUserLoginEvent;
use Drupal\openy_gc_log\Entity\LogEntityInterface;
use Drupal\openy_gc_log\Logger;
use Drupal\user\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class GCLogUserLogin Subscriber.
 *
 * @package Drupal\openy_gc_log\EventSubscriber
 */
class GCLogUserLoginSubscriber implements EventSubscriberInterface {

  /**
   * The Gated Content Logger.
   *
   * @var \Drupal\openy_gc_log\Logger
   */
  protected $gcLogger;

  /**
   * Constructs a new GCLogUserLoginSubscriber.
   *
   * @param \Drupal\openy_gc_log\Logger $gcLogger
   *   The Gated Content Logger.
   */
  public function __construct(Logger $gcLogger) {
    $this->gcLogger = $gcLogger;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      // Static class constant => method on this class.
      GCUserLoginEvent::EVENT_NAME => 'onUserLogin',
    ];
  }

  /**
   * Subscribe to the GC user login event dispatched.
   *
   * @param \Drupal\openy_gc_auth\Event\GCUserLoginEvent $event
   *   Event object.
   */
  public function onUserLogin(GCUserLoginEvent $event) {
    // Log user login.
    if ($event->account instanceof User) {
      $this->gcLogger->addLog([
        'email' => $event->account->getEmail(),
        'uid' => $event->account->id(),
        'event_type' => LogEntityInterface::EVENT_TYPE_LOGIN,
      ]);
    }
  }

}
