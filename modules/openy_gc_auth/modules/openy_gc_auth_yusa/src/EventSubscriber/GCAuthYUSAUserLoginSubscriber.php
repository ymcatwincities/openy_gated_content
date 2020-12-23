<?php

namespace Drupal\openy_gc_auth_yusa\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\openy_gc_auth\Event\GCUserLoginEvent;
use Drupal\user\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class GCAuthYUSAUserLoginSubscriber provides Y-USA login Subscriber.
 *
 * @package Drupal\openy_gc_auth_yusa\EventSubscriber
 */
class GCAuthYUSAUserLoginSubscriber implements EventSubscriberInterface {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new GCAuthYUSAUserLoginSubscriber.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
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
    if ($this->configFactory->get('openy_gc_auth.settings')->get('active_provider') == 'yusa') {
      $permissions_mapping = $this->configFactory->get('openy_gc_auth.provider.yusa')->get('permissions_mapping');
      if ($event->account instanceof User && !empty($event->extraData)) {
        $account = $event->account;
        if (isset($event->extraData['Memberships'])) {
          $account_roles = $account->getRoles();
          // Remove all virtual_y roles (in case if any changes in membership).
          foreach ($account_roles as $account_role) {
            if (strstr($account_role, 'virtual_y')) {
              $account->removeRole($account_role);
            }
          }
          $user_memberships = $event->extraData['Memberships'];
          $active_roles = [];
          $permissions_mapping = explode(';', $permissions_mapping);
          foreach ($permissions_mapping as $mapping) {
            $role = explode(':', $mapping);
            // Compare mapping roles with user membership.
            foreach ($user_memberships as $user_membership) {
              if (isset($role[0]) && $role[0] == $user_membership && isset($role[1])) {
                $active_roles[] = $role[1];
              }
            }
          }
          if (empty($active_roles)) {
            $active_roles = ['virtual_y'];
          }
          foreach ($active_roles as $role) {
            $account->addRole($role);
          }
          $account->save();
        }
      }
    }
  }

}
