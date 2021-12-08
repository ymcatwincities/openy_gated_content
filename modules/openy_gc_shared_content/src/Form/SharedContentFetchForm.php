<?php

namespace Drupal\openy_gc_shared_content\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\user\UserDataInterface;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays Shared Content Fetch UI.
 *
 * @internal
 */
class SharedContentFetchForm extends EntityForm {

  const PAGE_LIMIT = 20;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The Entity Repository.
   *
   * @var \Drupal\Core\Entity\EntityRepository
   */
  protected $entityRepository;

  /**
   * The plugin manager for SharedContentSourceType classes.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $sharedSourceTypeManager;

  /**
   * The pager manager.
   *
   * @var \Drupal\Core\Pager\PagerManagerInterface
   */
  protected $pagerManager;

  /**
   * Batch Builder.
   *
   * @var \Drupal\Core\Batch\BatchBuilder
   */
  protected $batchBuilder;

  /**
   * The user storage interface.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The user data interface.
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $userData;

  /**
   * The time interface.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * {@inheritdoc}
   */
  public function __construct(
      DateFormatter $date_formatter,
      EntityRepository $entity_repository,
      PluginManagerInterface $manager,
      PagerManagerInterface $pager_manager,
      UserStorageInterface $user_storage,
      UserDataInterface $user_data,
      TimeInterface $time
    ) {
    $this->dateFormatter = $date_formatter;
    $this->entityRepository = $entity_repository;
    $this->sharedSourceTypeManager = $manager;
    $this->pagerManager = $pager_manager;
    $this->batchBuilder = new BatchBuilder();
    $this->userStorage = $user_storage;
    $this->userData = $user_data;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('entity.repository'),
      $container->get('plugin.manager.shared_content_source_type'),
      $container->get('pager.manager'),
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('user.data'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $entity = $this->entity;

    if (!$entity->getUrl() || !$entity->getToken()) {
      $form['message'] = [
        '#type' => 'markup',
        '#markup' => $this->t('Source not configured! Go to <a href="@edit_link">source edit page</a>, set url and generate token.', [
          '@edit_link' => Url::fromRoute(
            'entity.shared_content_source_server.edit_form',
            ['shared_content_source_server' => $entity->id()],
            ['absolute' => TRUE])->toString(),
        ]),
      ];

      return $form;
    }

    $user = $this->currentUser();
    $user_data = $this->userData;
    $session_opened = $this->userStorage->load($user->id())->getLastLoginTime();
    $request_time = $this->time->getRequestTime();

    // Content is new when:
    // 1) the changed date > last time user fetched on their last session.
    // 2) we're on our first session.
    // 3) it hasn't already been previewed this session.
    //
    // In order to understand this we have three stored values:
    // - $first_fetched is set once and is used to validate the first session.
    // - $last_fetched is reset on every fetch.
    // - $last_session is reset on the first fetch of a new session and stores
    // the previous value of $last_fetched.
    $first_fetched = $user_data->get('openy_gc_shared_content', $user->id(), $entity->id() . '_first_fetched');
    $last_fetched = $user_data->get('openy_gc_shared_content', $user->id(), $entity->id() . '_last_fetched');
    $last_session = $user_data->get('openy_gc_shared_content', $user->id(), $entity->id() . '_last_session');
    $previewed = $user_data->get('openy_gc_shared_content', $user->id(), $entity->id() . '_previewed');

    // There are two cases that could mean we're on our first session:
    // 1) first_fetch is not set, OR
    // 2) first_fetch is the same session we're currently in.
    $first_session = !$first_fetched || $session_opened == $first_fetched;

    // Set the first_fetched_{server} the first time but then don't touch it.
    if (!$first_fetched) {
      $user_data->set('openy_gc_shared_content', $user->id(), $entity->id() . '_first_fetched', $session_opened);
    }

    // Set last_fetched_{server} each time we load the form.
    $user_data->set('openy_gc_shared_content', $user->id(), $entity->id() . '_last_fetched', $request_time);

    // Update the last session if we're starting a new session.
    // This will be what we check against the updated time later.
    if ($session_opened > $last_fetched) {
      // Need to do this to ensure we're not setting the value to NULL.
      $new_last_session = $last_fetched ?: 0;
      $user_data->set('openy_gc_shared_content', $user->id(), $entity->id() . '_last_session', $new_last_session);
      $last_session = $last_fetched;

      // Also reset the previewed array.
      $user_data->delete('openy_gc_shared_content',
        $user->id(),
        $entity->id() . '_previewed'
      );
      $previewed = [];
    }

    $form['label'] = [
      '#type' => 'markup',
      '#markup' => $entity->label() . ' - ' . $entity->getUrl(),
    ];

    $type = $this->getRouteMatch()->getParameter('type');
    $current_page = $this->getRequest()->query->get('page') ?? 0;
    $pager_query = [
      'page[offset]' => $current_page * self::PAGE_LIMIT,
      'page[limit]' => self::PAGE_LIMIT,
    ];
    $instance = $this->sharedSourceTypeManager->createInstance($type);
    $query_arg = array_merge($instance->getTeaserJsonApiQueryArgs(), $pager_query);
    $instance->applyFormFilters($query_arg, $this->getRequest());
    $source_data = $instance->jsonApiCall($this->entity, $query_arg);
    $form['fetched_data'] = [
      '#type' => 'container',
      '#prefix' => '<div id="fetched-data">',
      '#suffix' => '</div>',
    ];

    // Add filters according to selected plugin instance.
    $form['fetched_data']['filters'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['form--inline', 'clearfix'],
      ],
      'fields' => $instance->getFormFilters($this->entity->getUrl()),
      'filter_actions' => [
        '#type' => 'actions',
        'button' => [
          '#type' => 'submit',
          '#value' => $this->t('Apply'),
          '#name' => 'apply_filters',
          '#submit' => ['::applyFilters'],
        ],
      ],
    ];

    // Default values for fields.
    foreach (Element::children($form['fetched_data']['filters']['fields']) as $name) {
      $form['fetched_data']['filters']['fields'][$name]['#default_value'] = $this->getRequest()->query->get($name) ?? '';
    }

    if (empty($source_data['data'])) {
      $form['fetched_data']['message'] = [
        '#type' => 'markup',
        '#markup' => $this->t('No data for selected source content type.'),
      ];
    }
    else {
      // Duplicate the form actions into the action container in the header.
      $form['fetched_data']['actions'] = parent::actions($form, $form_state);
      unset($form['fetched_data']['actions']['delete']);
      $form['fetched_data']['actions']['submit']['#value'] = $this->t('Fetch to my site');
      $form['fetched_data']['actions']['submit']['#attributes']['class'][] = 'button--primary';

      $form['fetched_data']['content'] = [
        '#title' => $this->t('Select content to import'),
        '#type' => 'tableselect',
        '#header' => [
          'name' => $this->t('Name'),
          'donated_date' => $this->t('Donated on'),
          'donated_by' => $this->t('Donated by'),
          'count_of_downloads' => $this->t('YMCAs using content'),
          'operations' => [
            'data' => $this->t('Operations'),
          ],
        ],
        '#options' => [],
      ];

      foreach ($source_data['data'] as $item) {
        $item_exists = $this->entityRepository->loadEntityByUuid('node', $item['id']) ? TRUE : FALSE;
        $ops_classes = ['use-ajax', 'button'];
        $ops_classes[] = !$item_exists ?: 'is-disabled';
        $row_classes = [];

        $donated_date_formatted = '';
        $item_is_new = FALSE;
        if (!empty($item['attributes']['changed'])) {
          // Fetch the last modified date and format it.
          $changed = strtotime($item['attributes']['changed']);
          $donated_date_formatted = $this->dateFormatter->format($changed, 'short');

          // If the item is new then give the row the respective class.
          $item_is_new = (($changed > $last_session) || $first_session) && !in_array($item['id'], $previewed);
          // Add the class if the item is new AND it's not fetched yet.
          $row_classes[] = $item_is_new && !$item_exists ? 'new-item' : [];
        }

        $form['fetched_data']['content']['#options'][$item['id']] = [
          // @todo maybe we can highlight existing items here.
          '#disabled' => $item_exists,
          '#attributes' => [
            'class' => $row_classes,
          ],
          'name' => $instance->formatItem($item),
          'donated_date' => $donated_date_formatted,
          'donated_by' => !empty($item['attributes']['field_gc_origin']) ? $item['attributes']['field_gc_origin'] : ' ',
          'count_of_downloads' => !empty($item['attributes']['field_share_count']) ? $item['attributes']['field_share_count'] : '0',
          'operations' => [
            'data' => [
              '#type' => 'link',
              '#title' => $item_exists ? $this->t('Added') : $this->t('Preview'),
              '#url' => Url::fromRoute('entity.shared_content_source_server.preview', [
                'shared_content_source_server' => $entity->id(),
                'type' => $type,
                'uuid' => $item['id'],
              ]),
              '#attributes' => [
                'class' => $ops_classes,
              ],
            ],
          ],
        ];
      }

      // Create custom pager instead of build in drupal pager that throws
      // AJAX errors during navigation.
      $form['fetched_data']['pager'] = [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#items' => [],
        '#attributes' => ['class' => 'pager__items'],
        '#wrapper_attributes' => ['class' => 'pager'],
      ];
      $current_page_query = $this->getRequest()->query->all();
      if ($current_page != 0) {
        $form['fetched_data']['pager']['#items'][] = [
          '#title' => $this->t('prev'),
          '#type' => 'link',
          '#wrapper_attributes' => ['class' => 'pager__item'],
          '#url' => Url::fromRoute('<current>', [], [
            'query' => array_merge($current_page_query, ['page' => $current_page - 1]),
          ]),
        ];
      }
      $form['fetched_data']['pager']['#items'][] = [
        '#markup' => $current_page + 1,
        '#type' => 'markup',
        '#wrapper_attributes' => ['class' => 'pager__item'],
      ];
      if (count($source_data['data']) == self::PAGE_LIMIT) {
        $form['fetched_data']['pager']['#items'][] = [
          '#title' => $this->t('next'),
          '#type' => 'link',
          '#wrapper_attributes' => ['class' => 'pager__item'],
          '#url' => Url::fromRoute('<current>', [], [
            'query' => array_merge($current_page_query, ['page' => $current_page + 1]),
          ]),
        ];
      }
    }
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    unset($actions['delete']);
    $actions['submit']['#value'] = $this->t('Fetch to my site');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function applyFilters(array $form, FormStateInterface $form_state) {
    $input = $form_state->getUserInput();
    $query = ['page' => 0];
    foreach (Element::children($form['fetched_data']['filters']['fields']) as $name) {
      if (isset($input[$name])) {
        $query[$name] = $input[$name];
      }
    }
    $url = Url::fromRoute('<current>', [], ['query' => $query]);
    $form_state->setRedirectUrl($url);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $form_state->disableRedirect();
    if (!$form_state->getValue('content')) {
      $this->messenger()->addWarning($this->t('There no content to fetch.'));
      return;
    }
    $to_create = array_filter($form_state->getValue('content'));
    if (empty($to_create)) {
      $this->messenger()->addWarning($this->t('Please select items.'));
      return;
    }
    $type = $this->getRouteMatch()->getParameter('type');

    $this->batchBuilder
      ->setTitle($this->t('Fetching data'))
      ->setInitMessage($this->t('Initializing.'))
      ->setProgressMessage($this->t('Completed @current of @total.'))
      ->setErrorMessage($this->t('An error has occurred.'));
    $this->batchBuilder->setFile(drupal_get_path('module', 'openy_gc_shared_content') . '/src/Form/SharedContentFetchForm.php');
    foreach ($to_create as $uuid) {
      $this->batchBuilder->addOperation([$this, 'processItem'], [
        $this->entity->getUrl(),
        $uuid,
        $type,
      ]);
    }
    $this->batchBuilder->setFinishCallback([$this, 'finished']);
    batch_set($this->batchBuilder->toArray());
  }

  /**
   * Processor for batch operations.
   */
  public function processItem($url, $uuid, $type, array &$context) {
    $instance = $this->sharedSourceTypeManager->createInstance($type);
    $instance->saveFromSource($url, $uuid);
  }

  /**
   * Finished callback for batch.
   */
  public function finished($success, $results, $operations) {
    $this->messenger()->addStatus($this->t('Data Fetching Finished'));
  }

}
