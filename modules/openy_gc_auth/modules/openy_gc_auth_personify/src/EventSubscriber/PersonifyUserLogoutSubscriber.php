<?php

namespace Drupal\openy_gc_auth_personify\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\openy_gc_auth\Event\GCUserLogoutEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\openy_gc_auth_personify\LogoutClient;

/**
 * Class PersonifyUserLogoutSubscriber Subscriber.
 *
 * @package Drupal\openy_gc_auth_personify\EventSubscriber
 */
class PersonifyUserLogoutSubscriber implements EventSubscriberInterface {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $currentRequest;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Personify Client.
   *
   * @var \Drupal\openy_gc_auth_personify\LogoutClient
   */
  protected $logoutClient;

  /**
   * Constructs a new PersonifyUserLogoutSubscriber.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\openy_gc_auth_personify\LogoutClient $logoutClient
   *   Personify Logout Client.
   */
  public function __construct(
    RequestStack $requestStack,
    ConfigFactoryInterface $configFactory,
    LogoutClient $logoutClient
  ) {
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->configFactory = $configFactory;
    $this->logoutClient = $logoutClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      // Static class constant => method on this class.
      GCUserLogoutEvent::EVENT_NAME => 'onUserLogout',
    ];
  }

  /**
   * Subscribe to the GC user logout event dispatched.
   *
   * @param \Drupal\openy_gc_auth\Event\GCUserLogoutEvent $event
   *   Event object.
   */
  public function onUserLogout(GCUserLogoutEvent $event) {
    if ($this->configFactory->get('openy_gc_auth.settings')->get('active_provider') == 'personify') {
      $token = '';
      if ($this->currentRequest->cookies->has('Drupal_visitor_personify_authorized')) {
        $token = $this->currentRequest->cookies->get('Drupal_visitor_personify_authorized');
      }
      if (empty($token)) {
        return FALSE;
      }

      $isUserSuccessfullyLogout = $this->logoutClient->logout($token);
      if ($isUserSuccessfullyLogout) {
        user_cookie_delete('personify_authorized');
        user_cookie_delete('personify_time');
        return TRUE;
      }
      return FALSE;
    }
  }

}
