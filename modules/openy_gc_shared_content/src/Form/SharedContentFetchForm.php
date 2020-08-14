<?php

namespace Drupal\openy_gc_shared_content\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Pager\PagerManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays Shared Content Fetch UI.
 *
 * @internal
 */
class SharedContentFetchForm extends EntityForm {

  const PAGE_LIMIT = 4;

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
   * {@inheritdoc}
   */
  public function __construct(PluginManagerInterface $manager, PagerManagerInterface $pager_manager) {
    $this->sharedSourceTypeManager = $manager;
    $this->pagerManager = $pager_manager;
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

    if (!$entity->url || !$entity->token) {
      $form['message'] = [
        '#type' => 'markup',
        '#markup' => $this->t('Source not configured! Go to <path> and set url and token.'),
      ];

      return $form;
    }

    $form['label'] = [
      '#type' => 'markup',
      '#markup' => $entity->label() . ' - ' . $entity->url,
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
    $source_data = $instance->jsonApiCall($this->entity->url, $pager_query);
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
              '#type' => 'button',
              '#value' => $this->t('Preview'),
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
    $to_create = array_filter($form_state->getValue('content'));
    if (empty($to_create)) {
      $this->messenger()->addWarning($this->t('Please select items.'));
      return;
    }
    $type = $this->getRouteMatch()->getParameter('type');
    $instance = $this->sharedSourceTypeManager->createInstance($type);
    foreach ($to_create as $uuid) {
      // TODO: create items in batch.
      $instance->saveFromSource($this->entity->url, $uuid);
    }
    $this->messenger()->addStatus($this->t('Fetched.'));
  }

}
