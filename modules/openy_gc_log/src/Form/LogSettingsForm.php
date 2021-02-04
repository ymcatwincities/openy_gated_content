<?php

namespace Drupal\openy_gc_log\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Virtual Y Log Settings Form.
 */
class LogSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['openy_gc_log.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openy_gc_log_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('openy_gc_log.settings');
    $form['#tree'] = TRUE;

    $form['app_settings'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Virtual Y Log settings'),
    ];

    $form['app_settings']['activity_granularity_interval'] = [
      '#type' => 'select',
      '#title' => $this->t('Activity granularity interval'),
      '#description' => $this->t('Select the time period after which the new activity tracking session will be started for user.'),
      '#default_value' => $config->get('activity_granularity_interval'),
      '#required' => TRUE,
      '#options' => [
        300 => $this->t('5 minutes'),
        600 => $this->t('10 minutes'),
        900 => $this->t('15 minutes'),
        1200 => $this->t('20 minutes'),
        1800 => $this->t('30 minutes'),
        3600 => $this->t('1 hour'),
      ],
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
    $settings = $this->config('openy_gc_log.settings');
    $settings->setData($form_state->getValue('app_settings'));
    $settings->save();
    parent::submitForm($form, $form_state);
  }

}
