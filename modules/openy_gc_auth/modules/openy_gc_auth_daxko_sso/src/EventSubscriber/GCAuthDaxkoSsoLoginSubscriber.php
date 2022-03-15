<?php

namespace Drupal\openy_gc_auth_daxko_sso\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\daxko_sso\DaxkoSSOClient;
use Drupal\openy_gc_auth\Event\GCUserLoginEvent;
use Drupal\user\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class GCAuthDaxkoSsoLogin Subscriber.
 *
 * @package Drupal\openy_gc_auth_daxko_sso\EventSubscriber
 */
class GCAuthDaxkoSsoLoginSubscriber implements EventSubscriberInterface {

  /**
   * Daxko Client service instance.
   *
   * @var \Drupal\daxko_sso\DaxkoSSOClient
   */
  protected $daxkoClient;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new GCAuthDaxkoSsoLoginSubscriber.
   *
   * @param \Drupal\daxko_sso\DaxkoSSOClient $daxkoSSOClient
   *   Daxko client instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory.
   */
  public function __construct(DaxkoSSOClient $daxkoSSOClient, ConfigFactoryInterface $config) {
    $this->daxkoClient = $daxkoSSOClient;
    $this->configFactory = $config;
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
    $config = $this->configFactory->get('openy_gc_auth.provider.daxko_sso');

    // If the config is not set then there's nothing to do here.
    if ($config->get('virtual_branch_check_in') !== 1) {
      return;
    }

    // On VY user login.
    if ($event->account instanceof User) {
      // Get the user email.
      $email = $event->account->getEmail();

      // If we can parse a member id from the email...
      if (preg_match('/daxko-([\d-]+)@/', $email, $matches) === 1) {
        // Then post it to the Daxko Virtual Check In endpoint.
        $body = ['member_id' => $matches[1]];
        $this->daxkoClient->postRequest('virtualbranch/checkin', $body);
      }
    }
  }

}
