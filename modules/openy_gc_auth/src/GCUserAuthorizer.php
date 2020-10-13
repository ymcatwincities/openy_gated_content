<?php

namespace Drupal\openy_gc_auth;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\openy_gc_auth\Event\GCUserLoginEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * GCUserAuthorizer class.
 */
class GCUserAuthorizer {

  const VIRTUAL_Y_DEFAULT_ROLE = 'virtual_y';

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
   * GCUserAuthorizer constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EventDispatcherInterface $event_dispatcher) {
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->eventDispatcher = $event_dispatcher;
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
    // Activate user if it's not.
    if (!$account->isActive()) {
      $account->activate();
      $account->save();
    }

    if (!$account) {
      $user = $this->userStorage->create();
      $user->setPassword(user_password());
      $user->enforceIsNew();
      $user->setEmail($email);
      $user->setUsername($name);
      $user->addRole(self::VIRTUAL_Y_DEFAULT_ROLE);
      $user->activate();
      $result = $account = $user->save();
      if ($result) {
        $account = user_load_by_mail($email);
      }
    }
    // Instantiate GC login user event.
    $event = new GCUserLoginEvent($account);
    // Dispatch the event.
    $this->eventDispatcher->dispatch(GCUserLoginEvent::EVENT_NAME, $event);

    user_login_finalize($account);

  }

  /**
   * {@inheritdoc}
   */
  public function createUser($name, $email, $active) {
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
      $user->addRole(self::VIRTUAL_Y_DEFAULT_ROLE);
      if ($active) {
        $user->activate();
      }
      $result = $account = $user->save();
      if ($result) {
        $account = user_load_by_mail($email);
      }
    }

    return $account;

  }

}
