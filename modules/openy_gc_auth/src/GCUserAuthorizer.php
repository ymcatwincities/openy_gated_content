<?php

namespace Drupal\openy_gc_auth;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\openy_gc_auth\Event\GCUserLoginEvent;
use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * User Authorizer class.
 */
class GCUserAuthorizer {

  use StringTranslationTrait;

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
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Messenger service instance.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * GCUserAuthorizer constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   Logger factory.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   Messenger service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EventDispatcherInterface $event_dispatcher, LoggerChannelFactoryInterface $logger_channel_factory, Messenger $messenger) {
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->eventDispatcher = $event_dispatcher;
    $this->logger = $logger_channel_factory->get('gc_user_authorizer');
    $this->messenger = $messenger;
  }

  /**
   * Aauthorize user with provided data.
   *
   * @param string $name
   *   Username from service provider.
   * @param string $mail
   *   Email from service provider.
   * @param array $extra_data
   *   Array with optional data.
   *
   * @return bool
   *   True if the user successfully logged in.
   */
  public function authorizeUser($name, $mail, array $extra_data = []) {

    if (empty($name) || empty($mail)) {
      return FALSE;
    }

    $account = NULL;
    // Load drupal user by name and email from SalesForce.
    $account_by_mail = user_load_by_mail($mail);
    $account_by_name = user_load_by_name($name);

    // If we can load at least one account by userdata(from Salesforce).
    if ($account_by_mail || $account_by_name) {

      // If we have two account loaded, we should check if it's one user or two
      // different(f.e. case if user change email in service provider, but it's
      // already used in Drupal).
      if ($account_by_mail && $account_by_name) {
        // If for email and name from service provider registered two different
        // account. Should be resolved manually in Drupal.
        if ($account_by_mail->id() !== $account_by_name->id()) {
          $this->resolveAccountConflict($name, $mail);
        }
        // If both account point to same User.
        else {
          // Doesn't matter, what object to use, as they are the same.
          $account = $account_by_mail;
        }
      }
      // Loaded one account -> email or name was changed in Service provider.
      else {
        // Get existing(loaded) account, that will be logged in.
        $account = $account_by_mail ? $account_by_mail : $account_by_name;
        // Get changed in Service provider field, update and log changing of
        // related field in Drupal.
        // If $account_by_mail exist, this mean that by name we didn't load user
        // and name was changed in Service provider.
        $changed_field = $account_by_mail ? 'name' : 'mail';
        $old_changed_field_value = $account->get($changed_field)->value;
        // Set new value (from service provider).
        $account->set($changed_field, $$changed_field);
        $account->save();

        $this->logger->error('Service provider user credentials was changed. For User with id:%id changed field \'%field\' from \'%old\' to \'%new\' value',
          [
            '%id' => $account->id(),
            '%field' => $changed_field,
            '%old' => $old_changed_field_value,
            '%new' => $$changed_field,
          ]
        );
      }
    }
    else {
      $account = $this->createUser($name, $mail, TRUE);
    }

    if ($account) {
      $this->loginUser($account);
      return TRUE;
    }
  }

  /**
   * Login user into Drupal.
   *
   * @param \Drupal\user\UserInterface $account
   *   User object.
   * @param array $extra_data
   *   Array with optional data.
   */
  public function loginUser(UserInterface $account, array $extra_data = []) {
    // Activate user if it's not.
    if (!$account->isActive()) {
      $account->activate();
      $account->setPassword(user_password());
      $account->save();
    }

    // Instantiate GC login user event.
    $event = new GCUserLoginEvent($account, $extra_data);
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

  /**
   * Function to handle case when there is a conflict with the name and email.
   *
   * Logic (message) can be overridden in the child class.
   *
   * @param string $name
   *   Username from service provider.
   * @param string $mail
   *   Email from service provider.
   */
  protected function resolveAccountConflict($name, $mail) {
    $this->logger->error("For email %mail and name %name registered two different accounts in Virtual Y. Please contact to Virtual Y administrator.",
      [
        '%mail' => $name,
        '%name' => $mail,
      ]
    );
    $this->messenger->addError($this->t("For email %mail and name %name registered two different accounts in Virtual Y. Please contact to Virtual Y administrator.",
      [
        '%mail' => $name,
        '%name' => $mail,
      ])
    );
  }

}
