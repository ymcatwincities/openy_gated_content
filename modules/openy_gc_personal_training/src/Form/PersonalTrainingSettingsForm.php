<?php

namespace Drupal\openy_gc_personal_training\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Personal Training Settings Form.
 *
 * @ingroup openy_gc_personal_training
 */
class PersonalTrainingSettingsForm extends ConfigFormBase {

  /**
   * The Personal Training Provider plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $personalTrainingProviderManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(PluginManagerInterface $personal_training_provider) {
    $this->personalTrainingProviderManager = $personal_training_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.personal_training_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'personal_training_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['openy_gc_personal_training.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('openy_gc_personal_training.settings');
    $active_provider = $this->config('openy_gc_personal_training.settings')->get('active_provider');
    $plugin_definitions = $this->personalTrainingProviderManager->getDefinitions();
    if (empty($plugin_definitions)) {
      return ['#markup' => $this->t('Personal training providers not found.')];
    }

    $form['peerjs_domain'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PeerJS Domain'),
      '#default_value' => $config->get('peerjs_domain'),

      '#required' => TRUE,
    ];

    $form['peerjs_port'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PeerJS Port'),
      '#default_value' => $config->get('peerjs_port'),
      '#required' => TRUE,
    ];

    $form['peerjs_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('PeerJS Uri'),
      '#default_value' => $config->get('peerjs_uri'),
      '#required' => TRUE,
    ];

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
        '#url' => Url::fromRoute('openy_gc_personal_training.provider.edit', [
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
      $form_state->setErrorByName('providers', $this->t('Please select personal training provider!'));
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
    $settings = $this->config('openy_gc_personal_training.settings');
    $settings->set('active_provider', key($selected));
    $settings->set('peerjs_domain', $form_state->getValue('peerjs_domain'));
    $settings->set('peerjs_port', $form_state->getValue('peerjs_port'));
    $settings->set('peerjs_uri', $form_state->getValue('peerjs_uri'));
    $settings->save();
    parent::submitForm($form, $form_state);
  }

}
