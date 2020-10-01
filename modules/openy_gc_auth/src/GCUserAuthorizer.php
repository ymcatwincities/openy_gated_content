<?php

namespace Drupal\openy_gc_auth;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\openy_gc_auth\Event\GCUserLoginEvent;
use Drupal\openy_gc_log\Logger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * GCUserAuthorizer class.
 */
class GCUserAuthorizer {

  /**
   * User entity storage.
   *
   * @var \Drupal\User\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The Gated Content Logger.
   *
   * @var \Drupal\openy_gc_log\Logger
   */
  protected $gcLogger;

  /**
   * GCUserAuthorizer constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher.
   * @param \Drupal\openy_gc_log\Logger $gcLogger
   *   The Gated Content Logger.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EventDispatcherInterface $event_dispatcher, Logger $gcLogger = NULL) {
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->eventDispatcher = $event_dispatcher;
    $this->gcLogger = $gcLogger;
  }

  /**
   * {@inheritdoc}
   */
  public function authorizeUser($name, $email) {
    if (empty($name) || empty($email)) {
      return;
    }
    // Create drupal user if it doesn't exist and login it.
    $account = user_load_by_mail($email);

    if (!$account) {
      $user = $this->userStorage->create();
      $user->setPassword(user_password());
      $user->enforceIsNew();
      $user->setEmail($email);
      $user->setUsername($name);
      $user->addRole('virtual_y');
      $user->activate();
      $result = $account = $user->save();
      if ($result) {
        $account = user_load_by_mail($email);
      }
    }
    // Log user login.
    if ($this->gcLogger instanceof Logger) {
      $this->gcLogger->addLog([
        'email' => $email,
        'event_type' => 'userLoggedIn',
      ]);
    }

    // Instantiate GC login user event.
    $event = new GCUserLoginEvent($account);
    // Dispatch the event.
    $this->eventDispatcher->dispatch(GCUserLoginEvent::EVENT_NAME, $event);

    user_login_finalize($account);

  }

}
