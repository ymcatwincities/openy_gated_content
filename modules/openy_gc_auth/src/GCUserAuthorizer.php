<?php

namespace Drupal\openy_gc_auth;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\openy_gc_auth\Event\GCUserLoginEvent;
use Drupal\openy_gc_log\Logger;
use Drupal\user\Entity\User;
use Drupal\user\UserStorageInterface;

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
   * The Gated Content Logger.
   *
   * @var \Drupal\openy_gc_log\Logger
   */
  protected $gcLogger;

  /**
   * GCUserAuthorizer constructor.
   *
   * @param \Drupal\User\UserStorageInterface $user_storage
   *   User entity storage.
   * @param \Drupal\openy_gc_log\Logger $gcLogger
   *   The Gated Content Logger.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, Logger $gcLogger = NULL) {
    $this->userStorage = $entityTypeManager->getStorage('user');
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
    // Get the event_dispatcher service and dispatch the event.
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch(GCUserLoginEvent::EVENT_NAME, $event);

    user_login_finalize($account);
  }
}
