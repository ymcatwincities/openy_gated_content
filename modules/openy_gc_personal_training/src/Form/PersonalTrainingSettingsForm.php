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
    $form['#tree'] = FALSE;
    if (empty($plugin_definitions)) {
      return ['#markup' => $this->t('1on1 Meeting providers not found.')];
    }

    $form['peer_settings'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Peer server settings'),
    ];

    $form['peer_settings']['signalingServerPRL'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Signaling Server PRL (Protocol-relative URL)'),
      '#default_value' => $config->get('signalingServerPRL'),
      '#required' => FALSE,
      '#description' => $this->t('Used for web-socket connection.'),
    ];

    $form['peer_settings']['peerjs_stun'] = [
      '#type' => 'textfield',
      '#title' => $this->t('STUN Server Domain'),
      '#description' => $this->t('STUN server required to setup WebRTC connection.'),
      '#default_value' => $config->get('peerjs_stun'),
      '#required' => FALSE,
    ];

    $form['peer_settings']['peerjs_turn_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('TURN Server Domain'),
      '#description' => $this->t('TURN server required to setup WebRTC connection. Please, do not use test server from the internet. Your connection might be unstable!'),
      '#default_value' => $config->get('peerjs_turn_url'),
      '#required' => FALSE,
    ];

    $form['peer_settings']['peerjs_turn_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('TURN Username'),
      '#default_value' => $config->get('peerjs_turn_username'),
      '#description' => $this->t('Username from your TURN server config'),
      '#required' => FALSE,
    ];

    $form['peer_settings']['peerjs_turn_credential'] = [
      '#type' => 'textfield',
      '#title' => $this->t('TURN Password'),
      '#default_value' => $config->get('peerjs_turn_credential'),
      '#description' => $this->t('Password from your TURN server config'),
      '#required' => FALSE,
    ];

    $form['peer_settings']['peerjs_debug'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Debug level'),
      '#description' => $this->t('Set it to any number more than 0 to enable debugging'),
      '#default_value' => $config->get('peerjs_debug'),
      '#required' => FALSE,
    ];

    $form['peer_settings']['providers'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Active'),
        $this->t('Name'),
        $this->t('Action'),
      ],
    ];

    foreach ($plugin_definitions as $name => $definition) {
      $form['peer_settings']['providers'][$name]['active'] = [
        '#type' => 'checkbox',
        '#default_value' => $name == $active_provider,
      ];
      $form['peer_settings']['providers'][$name]['name'] = [
        '#markup' => $definition['label'],
      ];
      $form['peer_settings']['providers'][$name]['action'] = [
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

    $form['notifications_settings'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Notifications settings'),
    ];

    $form['notifications_settings']['delete'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Meeting delete'),
    ];

    $form['notifications_settings']['delete']['meeting_delete_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email subject'),
      '#default_value' => $config->get('meeting_delete_subject'),
      '#required' => TRUE,
    ];

    $form['notifications_settings']['delete']['meeting_delete_message'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Email message'),
      '#description' => $this->t('Available tokens: %meeting_title%, %meeting_start_date%'),
      '#default_value' => $config->get('meeting_delete_message'),
      '#required' => TRUE,
    ];

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
      $form_state->setErrorByName('providers', $this->t('Please select 1on1 Meeting provider!'));
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

    $settings->set('signalingServerPRL', $form_state->getValue('signalingServerPRL'));
    $settings->set('peerjs_stun', $form_state->getValue('peerjs_stun'));
    $settings->set('peerjs_turn_url', $form_state->getValue('peerjs_turn_url'));
    $settings->set('peerjs_turn_credential', $form_state->getValue('peerjs_turn_credential'));
    $settings->set('peerjs_turn_username', $form_state->getValue('peerjs_turn_username'));

    $settings->set('peerjs_debug', $form_state->getValue('peerjs_debug'));
    $settings->set('meeting_delete_subject', $form_state->getValue('meeting_delete_subject'));
    $settings->set('meeting_delete_message', $form_state->getValue('meeting_delete_message')['value']);

    $settings->save();
    parent::submitForm($form, $form_state);
  }

}
