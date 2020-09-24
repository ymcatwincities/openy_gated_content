<?php

namespace Drupal\openy_gc_auth\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\EventDispatcher\Event;
use Drupal\Core\Database\Database;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class VirtualYLoginRedirect.
 */
class VirtualYLoginRedirect implements EventSubscriberInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * ConfigFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new VirtualYLoginRedirect.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   The current route match.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(
    RouteMatchInterface $current_route_match,
    AccountProxyInterface $current_user,
    ConfigFactoryInterface $configFactory
  ) {
    $this->currentRouteMatch = $current_route_match;
    $this->currentUser = $current_user;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['kernel.response'] = ['checkForRedirect'];

    return $events;
  }

  /**
   * A method to be called whenever a kernel.response event is dispatched.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The event triggered by the response.
   */
  public function checkForRedirect(Event $event) {

    $route_name = $this->currentRouteMatch->getRouteName();
    $config = $this->configFactory->get('openy_gated_content.settings');

    switch ($route_name) {
      case 'entity.node.canonical':
        /** @var \Drupal\node\NodeInterface $node */
        $node = $this->currentRouteMatch->getParameter('node');

        $currentUser = $this->currentUser;

        if (
          $currentUser->isAnonymous()
          && $this->checkIfParagraphAtNode($node, 'gated_content')
        ) {
          $event->setResponse(new RedirectResponse($config->get('virtual_y_login_url')));
        }

        if (
          $currentUser->isAuthenticated()
          && $this->checkIfParagraphAtNode($node, 'gated_content_login')
        ) {
          $event->setResponse(new RedirectResponse($config->get('virtual_y_url')));
        }

    }
  }

  /**
   * Check if provided paragraph exists on the node.
   */
  private function checkIfParagraphAtNode(NodeInterface $node, $paragraph_id) {
    $connection = Database::getConnection();

    $result = $connection->select('paragraphs_item_field_data', 'pd')
      ->fields('pd', ['id'])
      ->condition('pd.parent_id', $node->id())
      ->condition('pd.type', $paragraph_id)
      ->countQuery()
      ->execute()
      ->fetchCol();
    return reset($result);
  }

}
