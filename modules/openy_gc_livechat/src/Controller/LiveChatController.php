<?php

namespace Drupal\openy_gc_livechat\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provide actions to update current user name.
 */
class LiveChatController extends ControllerBase {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * LiveChatController constructor.
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
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * Get current user name.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JsonResponse.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getLiveChatData() {
    $user_storage = $this->entityTypeManager()->getStorage('user');
    $user = $user_storage->load($this->currentUser()->id());
    $ratchet_settings = $this->configFactory->get('openy_gc_livechat.settings');

    $data = [
      'name' => $user->getAccountName(),
      'user_id' => $this->currentUser()->id(),
      'ratchet' => [
        'port' => $ratchet_settings->get('port'),
        'mode' => $ratchet_settings->get('mode'),
      ],
    ];

    return new JsonResponse($data, 200);
  }

  /**
   * Update current user name.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JsonResponse.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function updateName(Request $request) {
    $params = json_decode($request->getContent(), TRUE);
    if (!isset($params['name'])) {
      return new JsonResponse(['message' => 'Argument name missed'], 400);
    }
    $user_storage = $this->entityTypeManager()->getStorage('user');
    $user = $user_storage->load($this->currentUser()->id());

    if ($user->getAccountName() == $params['name']) {
      return new JsonResponse(['message' => 'There no updates in user name'], 200);
    }

    try {
      $user->setUsername($params['name']);
      $user->save();
    }
    catch (\Exception $exception) {
      return new JsonResponse(['message' => 'User with that username already exists'], 400);
    }

    return new JsonResponse(['message' => 'Username successfully updated'], 200);
  }

  /**
   * List logs for chats.
   */
  public function logsOverview() {
    $title = \Drupal::request()->query->get('title');
    $start_from = \Drupal::request()->query->get('start_from');
    $start_to = \Drupal::request()->query->get('start_to');

    $form['form'] = $this->formBuilder()->getForm('Drupal\openy_gc_livechat\Form\LogsSearchForm');

    $header = [
      'title' => $this->t('Title'),
      'start' => $this->t('Start'),
      'view' => $this->t('View'),
    ];

    $form['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => self::getChatlogs($title, $start_from, $start_to),
      '#empty' => $this->t('No records found'),
    ];

    $form['pager'] = [
      '#type' => 'pager'
    ];

    return $form;
  }

  /**
   * List chat messages of provided chat.
   */
  public function logDetailsOverview(Request $request, $cid) {
    $query = \Drupal::database()->select('openy_gc_livechat__chat_history', 'ch')
      ->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
      ->limit(50);
    $query->fields('ch');
    $query->condition('cid', $cid);
    $results = $query->execute()->fetchAll();

    $header = [
      'time' => $this->t('Time'),
      'username' => $this->t('Username'),
      'msg' => $this->t('Message'),
    ];

    $rows = [];
    foreach ($results as $row) {
      $rows[] = [
        'time' => DrupalDateTime::createFromTimestamp($row->created, date_default_timezone_get())->format('m-d-Y H:i:s'),
        'username' => $row->username,
        'msg' => $row->message,
      ];
    }

    return [
      '#caption' => isset($row->title) ? 'Messages history from: ' . $row->title : '',
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No records found'),
    ];
  }
  /**
   * Get unique chat logs records.
   */
  private function getChatlogs($title = null, $start_from = null, $start_to = null) {
    $list = [];
    $query = \Drupal::database()->select('openy_gc_livechat__chat_history', 'ch')
      ->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
      ->limit(10);
    $query->distinct();
    $query->fields('ch', ['cid', 'title', 'start']);
    if (!empty($title)) {
      $query->condition('title', $title);
    }
    if (!empty($start_from)) {
      $query->condition('start', $start_from, '>=');
    }
    if (!empty($start_to)) {
      $query->condition('start', $start_to, '<=');
    }
    $results = $query->execute()->fetchAll();
    foreach ($results as $row) {
      $view = Link::fromTextAndUrl(t('View'), Url::fromUserInput('/admin/virtual-y/chats/' . $row->cid));

      $list[] = [
        'title' => $row->title,
        'start' => $row->start,
        'view' => $view,
      ];
    }
    return $list;
  }
}
