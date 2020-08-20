<?php

namespace Drupal\openy_gc_shared_content\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays Shared Content Fetch UI.
 *
 * @internal
 */
class SharedContentFetchForm extends EntityForm {

  const PAGE_LIMIT = 20;

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
   * {@inheritdoc}
   */
  public function __construct(PluginManagerInterface $manager, PagerManagerInterface $pager_manager) {
    $this->sharedSourceTypeManager = $manager;
    $this->pagerManager = $pager_manager;
    $this->batchBuilder = new BatchBuilder();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.shared_content_source_type'),
      $container->get('pager.manager')
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

    $form['label'] = [
      '#type' => 'markup',
      '#markup' => $entity->label() . ' - ' . $entity->getUrl(),
    ];

    $type = $this->getRouteMatch()->getParameter('type');
    // JSON:API module does not provide a count because it would severely
    // degrade performance, so we use here 1000 as total items count.
    $pager = $this->pagerManager->createPager(1000, self::PAGE_LIMIT);
    $current_page = $pager->getCurrentPage();
    $pager_query = [
      'page[offset]' => $current_page * self::PAGE_LIMIT,
      'page[limit]' => self::PAGE_LIMIT,
    ];
    $instance = $this->sharedSourceTypeManager->createInstance($type);
    $query_arg = array_merge($instance->getTeaserJsonApiQueryArgs(), $pager_query);
    $source_data = $instance->jsonApiCall($this->entity->getUrl(), $query_arg);
    $form['fetched_data'] = [
      '#type' => 'container',
    ];

    if (empty($source_data)) {
      $form['fetched_data']['message'] = [
        '#type' => 'markup',
        '#markup' => $this->t('No data for selected source content type.'),
      ];
    }
    else {
      $form['fetched_data']['content'] = [
        '#title' => $this->t('Select content to import'),
        '#type' => 'tableselect',
        '#header' => [
          'name' => $this->t('Name'),
          'operations' => [
            'data' => $this->t('Operations'),
          ],
        ],
        '#options' => [],
      ];

      foreach ($source_data['data'] as $item) {
        $form['fetched_data']['content']['#options'][$item['id']] = [
          // TODO: maybe we can highlight existing items here.
          'name' => $instance->formatItem($item),
          'operations' => [
            'data' => [
              '#type' => 'link',
              '#title' => $this->t('Preview'),
              '#url' => Url::fromRoute('entity.shared_content_source_server.preview', [
                'shared_content_source_server' => $entity->id(),
                'type' => $type,
                'uuid' => $item['id'],
              ]),
              '#attributes' => [
                'class' => ['use-ajax', 'button'],
              ],
            ],
          ],
        ];
      }

      if (count($source_data['data']) < self::PAGE_LIMIT) {
        // Fix pager when there no next page.
        $this->pagerManager->createPager($current_page * self::PAGE_LIMIT, self::PAGE_LIMIT);
      }
      $form['fetched_data']['pager'] = [
        '#type' => 'pager',
        '#quantity' => 1,
      ];
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
