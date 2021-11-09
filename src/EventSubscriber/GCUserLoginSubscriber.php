<?php

namespace Drupal\openy_gated_content\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\openy_gated_content\GCUserService;
use Drupal\openy_gc_auth\Event\GCUserLoginEvent;
use Drupal\openy_gc_auth\GCIdentityProviderManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class GCLogUserLogin Subscriber.
 *
 * @package Drupal\openy_gated_content\EventSubscriber
 */
class GCUserLoginSubscriber implements EventSubscriberInterface {

  /**
   * The Gated Content User Service.
   *
   * @var \Drupal\openy_gated_content\GCUserService
   */
  protected $gcUserService;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The gated content authentication manager.
   *
   * @var \Drupal\openy_gc_auth\GCIdentityProviderManager
   */
  protected $authManager;

  /**
   * Constructs a new GCUserLoginSubscriber.
   *
   * @param \Drupal\openy_gated_content\GCUserService $gcUserService
   *   The Gated Content User Service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\openy_gc_auth\GCIdentityProviderManager $auth_manager
   *   The gated content authentication manager.
   */
  public function __construct(
    GCUserService $gcUserService,
    ConfigFactoryInterface $configFactory,
    GCIdentityProviderManager $auth_manager
  ) {
    $this->gcUserService = $gcUserService;
    $this->configFactory = $configFactory;
    $this->authManager = $auth_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
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
    if (!($event->account instanceof AccountInterface)) {
      return;
    }
    if ($event->account->getLastAccessedTime() > 0) {
      return;
    }
    $welcome_email_config = $this->configFactory->get('openy_gated_content.welcome_email_settings');
    if (!$welcome_email_config->get('enabled')) {
      return;
    }
    $gc_auth_config = $this->configFactory->get('openy_gc_auth.settings');
    $active_provider = $gc_auth_config->get('active_provider');
    $plugin_definition = $this->authManager->getDefinition($active_provider, TRUE);
    if (!$plugin_definition) {
      return;
    }
    /** @var \Drupal\openy_gc_auth\GCIdentityProviderInterface $plugin_instance */
    $plugin_instance = $this->authManager->createInstance($active_provider);
    $plugin_instance->sendWelcomeEmail($event->account);
  }

}
