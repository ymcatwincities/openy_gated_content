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
        'http' => $this->t('Http'),
        'https' => $this->t('Https'),
      ],
      '#default_value' => $this->config('openy_gc_livechat.settings')->get('mode'),
    ];
    $form['env'] = [
      '#title' => $this->t('Environment:'),
      '#type' => 'radios',
      '#options' => [
        'local' => $this->t('Local'),
        'prod' => $this->t('Production'),
      ],
      '#description' => $this->t('Use when https mode is enabled for any live server.'),
      '#default_value' => $this->config('openy_gc_livechat.settings')->get('env'),
    ];
    $form['port'] = [
      '#title' => $this->t('Port:'),
      '#type' => 'textfield',
      '#description' => $this->t('Port using by Websocket server for making connections.'),
      '#default_value' => $this->config('openy_gc_livechat.settings')->get('port'),
    ];
    $form['cert_path'] = [
      '#title' => $this->t('Path to public key:'),
      '#type' => 'textfield',
      '#description' => $this->t('Path to https certificate (public) on your server (in .pem format).'),
      '#default_value' => $this->config('openy_gc_livechat.settings')->get('cert_path'),
    ];
    $form['key_path'] = [
      '#title' => $this->t('Path to private key:'),
      '#type' => 'textfield',
      '#description' => $this->t('Path to https the private certificate on your server (in .pem format).'),
      '#default_value' => $this->config('openy_gc_livechat.settings')->get('key_path'),
    ];
    $form['scheduled'] = [
      '#title' => $this->t('Scheduled time of cron run:'),
      '#type' => 'textfield',
      '#description' => $this->t('Default is set to 01:00.'),
      '#placeholder' => '01:00',
      '#default_value' => $this->config('openy_gc_livechat.settings')->get('scheduled'),
    ];
    $form['interval'] = [
      '#title' => $this->t('How many days chat logs must be saved?'),
      '#type' => 'textfield',
      '#description' => $this->t('Default is set to 30 days.'),
      '#placeholder' => '30',
      '#default_value' => $this->config('openy_gc_livechat.settings')->get('interval'),
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
    $settings->set('scheduled', $form_state->getValue('scheduled'));
    $settings->set('interval', $form_state->getValue('interval'));
    $settings->save();
    parent::submitForm($form, $form_state);
  }

}
