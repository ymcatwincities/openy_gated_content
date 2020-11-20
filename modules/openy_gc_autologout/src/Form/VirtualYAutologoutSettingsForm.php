<?php

namespace Drupal\openy_gc_autologout\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings Form for openy_gc_autologout.
 */
class VirtualYAutologoutSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openy_gc_autologout_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'openy_gc_autologout.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('openy_gc_autologout.settings');

    $form['autologout_timeout'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Timeout'),
      '#default_value' => $config->get('autologout_timeout'),
      '#description' => $this->t('Provide timeout in seconds.'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $settings = $this->config('openy_gc_autologout.settings');
    $value = $form_state->getValue('autologout_timeout');
    $settings->set('autologout_timeout', $value);
    $settings->save();
  }

}
