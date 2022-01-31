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
    $form['port'] = [
      '#title' => $this->t('Port'),
      '#type' => 'textfield',
      '#description' => 'Port using by Websocket server for making connections.',
      '#default_value' => $this->config('openy_gc_livechat.settings')->get('port'),
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
    $settings->set('port', $form_state->getValue('port'));
    $settings->save();
    parent::submitForm($form, $form_state);
  }

}
