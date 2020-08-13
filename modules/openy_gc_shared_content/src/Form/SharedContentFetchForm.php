<?php

namespace Drupal\openy_gc_shared_content\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays Shared Content Fetch UI.
 *
 * @internal
 */
class SharedContentFetchForm extends EntityForm {

  /**
   * The plugin manager for SharedContentSourceType classes.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $sharedSourceTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(PluginManagerInterface $manager) {
    $this->sharedSourceTypeManager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.shared_content_source_type')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $entity = $this->entity;
    $options = [];

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

    foreach ($this->sharedSourceTypeManager->getDefinitions() as $plugin_id => $plugin) {
      $instance = $this->sharedSourceTypeManager->createInstance($plugin_id);
      $options[$instance->getId()] = $instance->getLabel();
    }
    // Use first item from options as default type.
    reset($options);
    $default_type = key($options);
    $type = $form_state->getValue('type') == NULL ? $default_type : $form_state->getValue('type');
    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content type'),
      '#default_value' => $default_type,
      '#options' => $options,
      '#ajax' => [
        'callback' => '::fetchSourceDataAjax',
        'wrapper' => 'fetched-data',
        'effect' => 'fade',
        'event' => 'change',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Loading content..'),
        ],
      ],
    ];

    // TODO: add pager.
    $instance = $this->sharedSourceTypeManager->createInstance($type);
    $source_data = $instance->jsonApiCall($this->entity->url);
    $form['fetched_data'] = [
      '#type' => 'container',
      '#prefix' => '<div id="fetched-data">',
      '#suffix' => '</div>',
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
        '#type' => 'checkboxes',
        '#options' => [],
      ];

      foreach ($source_data['data'] as $item) {
        $form['fetched_data']['content']['#options'][$item['id']] = $instance->formatItem($item);
      }
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
  public function fetchSourceDataAjax(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
    return $form['fetched_data'];
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
    $type = $form_state->getValue('type');
    $instance = $this->sharedSourceTypeManager->createInstance($type);
    foreach ($to_create as $uuid) {
      // TODO: create items in batch.
      $instance->saveFromSource($this->entity->url, $uuid);
    }
    $this->messenger()->addStatus($this->t('Fetched.'));
  }

}
