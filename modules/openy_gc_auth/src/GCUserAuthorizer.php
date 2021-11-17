<?php

namespace Drupal\openy_gc_auth;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Password\PasswordGeneratorInterface;
use Drupal\openy_gc_auth\Event\GCUserLoginEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * User Authorizer class.
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
   * The service for generating passwords.
   *
   * @var \Drupal\Core\Password\PasswordGeneratorInterface
   */
  protected $passwordGenerator;

  /**
   * GCUserAuthorizer constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher.
   * @param \Drupal\Core\Password\PasswordGeneratorInterface $password_generator
   *   Service for generating passwords.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EventDispatcherInterface $event_dispatcher, PasswordGeneratorInterface $password_generator) {
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->eventDispatcher = $event_dispatcher;
    $this->passwordGenerator = $password_generator;
  }

  /**
   * {@inheritdoc}
   */
  public function authorizeUser($name, $email, array $extra_data = []) {
    if (empty($name) || empty($email)) {
      return;
    }

    // Try to find the user by an email, if not -- then re-search by name.
    $account = user_load_by_mail($email);
    if (!$account && ($account = user_load_by_name($name))) {
      $account->setEmail($email);
      $account->save();
    }

    // Create drupal user if it doesn't exist and login it.
    if (!$account) {
      $user = $this->userStorage->create();
      $user->setPassword($this->passwordGenerator->generate());
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
    else {
      // Activate user if it's not.
      if (!$account->isActive()) {
        $account->activate();
        $account->setPassword($this->passwordGenerator->generate());
        $account->save();
      }
    }
    // Instantiate GC login user event.
    $event = new GCUserLoginEvent($account, $extra_data);
    // Dispatch the event.
    $this->eventDispatcher->dispatch($event, GCUserLoginEvent::EVENT_NAME);

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
      $user->setPassword($this->passwordGenerator->generate());
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
