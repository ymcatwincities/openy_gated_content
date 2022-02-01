<?php

namespace Drupal\openy_gc_livechat\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Identity Provider Edit Form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['openy_gc_livechat.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openy_gc_auth_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['mode'] = [
      '#title' => $this->t('Mode:'),
      '#type' => 'radios',
      '#options' => [
        'http' => 'Http',
        'https' => 'Https'
      ],
      '#default_value' => $this->config('openy_gc_livechat.settings')->get('mode'),
    ];
    $form['env'] = [
      '#title' => $this->t('Environment:'),
      '#type' => 'radios',
      '#options' => [
        'local' => 'Local',
        'prod' => 'Production'
      ],
      '#description' => 'Use when https mode is enabled for any live server.',
      '#default_value' => $this->config('openy_gc_livechat.settings')->get('env'),
    ];
    $form['port'] = [
      '#title' => $this->t('Port:'),
      '#type' => 'textfield',
      '#description' => 'Port using by Websocket server for making connections.',
      '#default_value' => $this->config('openy_gc_livechat.settings')->get('port'),
    ];
    $form['cert_path'] = [
      '#title' => $this->t('Path to public key:'),
      '#type' => 'textfield',
      '#description' => 'Path to https certificate (public) in .pem format.',
      '#default_value' => $this->config('openy_gc_livechat.settings')->get('cert_path'),
    ];
    $form['key_path'] = [
      '#title' => $this->t('Path to private key:'),
      '#type' => 'textfield',
      '#description' => 'Path to https certificate (private) in .pem format.',
      '#default_value' => $this->config('openy_gc_livechat.settings')->get('key_path'),
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $settings = $this->config('openy_gc_livechat.settings');
    $settings->set('mode', $form_state->getValue('mode'));
    $settings->set('env', $form_state->getValue('env'));
    $settings->set('port', $form_state->getValue('port'));
    $settings->set('cert_path', $form_state->getValue('cert_path'));
    $settings->set('key_path', $form_state->getValue('key_path'));
    $settings->save();
    parent::submitForm($form, $form_state);
  }

}
