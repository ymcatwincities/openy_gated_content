<?php

namespace Drupal\openy_gc_auth\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Identity Provider Edit Form.
 */
class GCAuthSettingsForm extends ConfigFormBase {

  /**
   * The Identity Provider plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $identityProviderManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(PluginManagerInterface $gc_identity_provider_manager) {
    $this->identityProviderManager = $gc_identity_provider_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.gc_identity_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['openy_gc_auth.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'interaction_type_edit';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $active_provider = $this->config('openy_gc_auth.settings')->get('active_provider');
    $plugin_definitions = $this->identityProviderManager->getDefinitions();
    if (empty($plugin_definitions)) {
      return ['#markup' => $this->t('Identity providers not found.')];
    }

    $form['providers'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Active'),
        $this->t('Name'),
        $this->t('Action'),
      ],
    ];

    foreach ($plugin_definitions as $name => $definition) {
      $form['providers'][$name]['active'] = [
        '#type' => 'checkbox',
        '#default_value' => $name == $active_provider,
      ];
      $form['providers'][$name]['name'] = [
        '#markup' => $definition['label'],
      ];
      $form['providers'][$name]['action'] = [
        '#title' => $this->t('Edit'),
        '#type' => 'link',
        '#url' => Url::fromRoute('openy_gc_auth.provider.edit', [
          'type' => $name,
        ]),
        '#attributes' => [
          'class' => ['button', 'button--small'],
        ],
      ];
    }

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $providers = $form_state->getValue('providers');
    $selected = array_filter($providers, function ($var) {
      return $var['active'] == 1;
    });
    if (empty($selected)) {
      $form_state->setErrorByName('providers', $this->t('Please select identity provider!'));
    }
    if (count($selected) > 1) {
      $form_state->setErrorByName('providers', $this->t('There should be only one active provider!'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $providers = $form_state->getValue('providers');
    $selected = array_filter($providers, function ($var) {
      return $var['active'] == 1;
    });
    reset($selected);
    $settings = $this->config('openy_gc_auth.settings');
    $settings->set('active_provider', key($selected));
    $settings->save();
    parent::submitForm($form, $form_state);
  }

}
