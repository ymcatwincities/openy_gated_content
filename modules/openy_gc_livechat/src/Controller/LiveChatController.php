<?php

namespace Drupal\openy_gc_livechat\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Link;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
   * The database connection used to store flood event information.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The currently active request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * LiveChatController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection which will be used to store the flood event
   *   information.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The currently active request object.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(ConfigFactoryInterface $configFactory, Connection $connection, Request $request, StateInterface $state) {
    $this->configFactory = $configFactory;
    $this->connection = $connection;
    $this->request = $request;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('database'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('state')
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
    $user_roles = $this->currentUser()->getRoles();
    $isInstructorRole = FALSE;
    if (in_array('virtual_trainer', $user_roles)) {
      $isInstructorRole = TRUE;
    }
    $data = [
      'name' => $user->getAccountName(),
      'user_id' => $this->currentUser()->id(),
      'isInstructorRole' => $isInstructorRole,
      'disabledLivechats' => $this->state->get('disabledVirtualChatrooms', []),
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
    $title = $this->request->query->get('title');
    $start_from = $this->request->query->get('start_from');
    $start_to = $this->request->query->get('start_to');

    $form['form'] = $this->formBuilder()->getForm('Drupal\openy_gc_livechat\Form\LogsSearchForm');

    $header = [
      'title' => $this->t('Title'),
      'start' => $this->t('Start'),
      'view' => $this->t('View'),
    ];

    $form['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $this->getChatlogs($title, $start_from, $start_to),
      '#empty' => $this->t('No records found'),
    ];

    $form['pager'] = [
      '#type' => 'pager',
    ];

    return $form;
  }

  /**
   * List chat messages of provided chat.
   */
  public function logDetailsOverview(Request $request, $cid) {
    $query = $this->connection->select('openy_gc_livechat__chat_history', 'ch')
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
  private function getChatlogs($title = NULL, $start_from = NULL, $start_to = NULL) {
    $list = [];
    $query = $this->connection->select('openy_gc_livechat__chat_history', 'ch')
      ->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
      ->limit(10);
    $query->distinct();
    $query->fields('ch', ['cid', 'title', 'start']);
    $start_to = DrupalDateTime::createFromFormat('Y-m-d', $start_to)->modify('+1 day')->format('Y-m-d');
    if (!empty($title)) {
      $query->condition('title', $title);
    }
    if (!empty($start_from)) {
      $query->condition('start', $start_from, '>=');
    }
    if (!empty($start_to)) {
      $query->condition('start', $start_to, '<');
    }
    $results = $query->execute()->fetchAll();
    foreach ($results as $row) {
      $view = Link::fromTextAndUrl($this->t('View'), Url::fromUserInput('/admin/virtual-y/chats/' . $row->cid));

      $list[] = [
        'title' => $row->title,
        'start' => DrupalDateTime::createFromFormat(
          'Y-m-d\TH:i:s',
          str_replace('.000Z', '', $row->start),
          date_default_timezone_get())->format('m/d/Y H:i'
        ),
        'view' => $view,
      ];
    }
    return $list;
  }

}
