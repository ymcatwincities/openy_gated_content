<?php

namespace Drupal\openy_gc_auth\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\openy_gc_auth\Event\GCUserLogoutEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Auth controller.
 */
class GCAuthController extends ControllerBase {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * GCAuthController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    EventDispatcherInterface $event_dispatcher
  ) {
    $this->configFactory = $configFactory;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * LogoutUser route.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function logoutUser(Request $request) {
    // Instantiate GC logout user event.
    $event = new GCUserLogoutEvent();
    // Dispatch the event.
    $this->eventDispatcher->dispatch(GCUserLogoutEvent::EVENT_NAME, $event);
    // Logout user from Drupal.
    user_logout();
    // Optionally redirect user after logout.
    $virtual_y_logout_url = $this->configFactory->get('openy_gated_content.settings')->get('virtual_y_logout_url');
    $redirect_url = !empty($virtual_y_logout_url) ? $virtual_y_logout_url : Url::fromRoute('<front>')->toString();
    $redirect = new TrustedRedirectResponse($redirect_url);
    $redirect->send();
    exit();
  }

}
