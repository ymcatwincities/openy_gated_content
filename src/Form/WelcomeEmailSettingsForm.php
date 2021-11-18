<?php

namespace Drupal\openy_gated_content\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Welcome Email Settings Form.
 */
class WelcomeEmailSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['openy_gated_content.welcome_email_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openy_gc_welcome_email_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('openy_gated_content.welcome_email_settings');

    $form['disclaimer'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['messages', 'messages--warning'],
      ],
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
      'message' => [
        '#type' => 'markup',
        '#markup' => $this->t('Mail system should be configured first to make this feature work'),
      ],
    ];

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Send welcome email on first login'),
      '#description' => $this->t('Check this box if you want to enable welcome email sending on user first login.'),
      '#default_value' => $config->get('enabled'),
    ];

    $form['email_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email subject'),
      '#description' => $this->t('This text will be used as an email subject.<br>This field supports tokens. Please, refer to the "Browse available tokens" link below.'),
      '#default_value' => $config->get('email_subject'),
      '#required' => TRUE,
    ];

    $form['email_body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Email message'),
      '#description' => $this->t('This text will be used in the email.<br>This field supports tokens. Please, refer to the "Browse available tokens" link below.'),
      '#default_value' => $config->get('email_body'),
      '#required' => TRUE,
    ];

    $form['tokens'] = [
      '#theme' => 'token_tree_link',
      '#text' => $this->t('Browse available tokens'),
      '#token_types' => ['user'],
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
    $settings = $this->config('openy_gated_content.welcome_email_settings');
    $settings->set('enabled', $form_state->getValue('enabled'));
    $settings->set('email_subject', $form_state->getValue('email_subject'));
    $settings->set('email_body', $form_state->getValue('email_body')['value']);
    $settings->save();

    parent::submitForm($form, $form_state);
  }

}
