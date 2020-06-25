<?php

namespace Drupal\openy_gated_content\EventSubscriber;

use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Gated contend entities pages access checking.
 */
class GatedContentSubscriber implements EventSubscriberInterface {

  /**
   * The current active user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a GatedContent Event Subscriber.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   The current active user.
   */
  public function __construct(AccountProxyInterface $user) {
    $this->currentUser = $user;
  }

  /**
   * Check Access to gated contend entities pages.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The GetResponseEvent to process.
   */
  public function accessCheck(GetResponseEvent $event) {
    $request = $event->getRequest();
    $route_name = $request->get('_route');
    $protected_routes = [
      'entity.node.canonical',
      'entity.eventseries.canonical',
      'entity.eventinstance.canonical',
    ];

    if (!in_array($route_name, $protected_routes)) {
      return;
    }

    $route_object = NULL;
    switch ($route_name) {
      case 'entity.node.canonical':
        $route_object = $request->get('node');
        break;

      case 'entity.eventseries.canonical':
        $route_object = $request->get('eventseries');
        break;

      case 'entity.eventinstance.canonical':
        $route_object = $request->get('eventinstance');
        break;
    }

    $entity_types = [
      'gc_video',
      'vy_blog_post',
      'live_stream',
      'virtual_meeting',
    ];
    if (!$route_object || !in_array($route_object->getType(), $entity_types)) {
      return;
    }

    if (!$this->currentUser->hasPermission('view gated content entities pages')) {
      throw new AccessDeniedHttpException('This page is not available.');
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // We need to have a priority of 31 or less to have the route available.
    $events[KernelEvents::REQUEST][] = ['accessCheck', 30];
    return $events;
  }

}
