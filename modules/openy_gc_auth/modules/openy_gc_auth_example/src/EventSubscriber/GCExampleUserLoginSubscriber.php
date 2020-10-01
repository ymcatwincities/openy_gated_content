<?php

namespace Drupal\openy_gc_auth_example\EventSubscriber;

use Drupal\openy_gc_auth\Event\GCUserLoginEvent;
use Drupal\user\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class GCExampleUserLoginSubscriber.
 *
 * @package Drupal\openy_gc_auth_example\EventSubscriber
 */
class GCExampleUserLoginSubscriber implements EventSubscriberInterface {

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

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
    // Alter Virtual Y related roles.
    if ($event->account instanceof User) {
      $roles = $event->account->getRoles();
      // Remove role in case if you need to change any.
      if (in_array('virtual_y', $roles)) {
        $event->account->removeRole('virtual_y');
        // Add new roles e.g. virtual_y, virtual_y_premium, virtual_y_trial, etc.
        // Let's keep the same default one for example.
        $event->account->addRole('virtual');
        $event->account->save();
      }
    }

  }

}
