<?php


namespace Drupal\openy_gc_auth\EventSubscriber;

use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\EventDispatcher\Event;
use Drupal\Core\Database\Database;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VirtualYLoginRedirect implements EventSubscriberInterface {

  /**
   * CurrentRouteMatch definition.
   *
   * @var Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructor.
   */
  public function __construct(CurrentRouteMatch $current_route_match, AccountProxyInterface $current_user) {
    $this->routeMatch = $current_route_match;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events['kernel.response'] = ['checkForRedirect'];

    return $events;
  }

  public function checkForRedirect(Event $event) {

    $route_name = $this->routeMatch->getRouteName();

    switch ($route_name) {
      case 'entity.node.canonical':
        /** @var \Drupal\node\NodeInterface $node */
        $node = $this->routeMatch->getParameter('node');

        $currentUser = $this->currentUser;

        if (
          $currentUser->isAnonymous()
          && $this->checkIfParagraphAtNode($node, 'gated_content')
        ) {
          $event->setResponse(new RedirectResponse('/virtual-y-login'));
        }

        if (
          $currentUser->isAuthenticated()
          && $this->checkIfParagraphAtNode($node, 'gated_content_login')
        ) {
          $event->setResponse(new RedirectResponse('/virtual-ymca'));
        }

    }
  }

  private function checkIfParagraphAtNode(NodeInterface $node, $paragraph_id) {
    $connection = Database::getConnection();

    return reset($connection->select('paragraphs_item_field_data', 'pd')
      ->fields('pd', ['id'])
      ->condition('pd.parent_id', $node->id())
      ->condition('pd.type', $paragraph_id)
      ->countQuery()
      ->execute()
      ->fetchCol());
  }


}