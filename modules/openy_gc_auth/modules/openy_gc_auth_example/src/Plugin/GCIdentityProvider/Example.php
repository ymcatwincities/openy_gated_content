<?php

namespace Drupal\openy_gc_auth_example\Plugin\GCIdentityProvider;

use Drupal\Core\Form\FormStateInterface;
use Drupal\openy_gc_auth\GCIdentityProviderPluginBase;

/**
 * Example identity provider plugin.
 *
 * @GCIdentityProvider(
 *   id="dummy",
 *   label = @Translation("Dummy provider"),
 *   config="openy_gc_auth.provider.dummy"
 * )
 */
class Example extends GCIdentityProviderPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration():array {
    return [
      'user' => '',
      'token' => '',
      'password' => '',
      'redirect_url' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['user'] = [
      '#title' => $this->t('User'),
      '#description' => $this->t('User name/email for access dummy identity provider.'),
      '#type' => 'textfield',
      '#default_value' => $config['user'],
      '#required' => TRUE,
    ];

    $form['token'] = [
      '#title' => $this->t('Api token'),
      '#description' => $this->t('Some dummy API token, that will be used on frontend.'),
      '#type' => 'textfield',
      '#default_value' => $config['token'],
      '#required' => TRUE,
    ];

    $form['password'] = [
      '#title' => $this->t('Password'),
      '#description' => $this->t('User password for access dummy identity provider. We recommend to not store this is configs, you can override this value in settings.php (config - openy_gc_auth.provider.example)'),
      '#type' => 'password',
      '#default_value' => $config['password'],
      '#size' => 25,
    ];

    $form['redirect_url'] = [
      '#title' => $this->t('Url'),
      '#description' => $this->t('Identity provider url for redirect.'),
      '#type' => 'textfield',
      '#default_value' => $config['redirect_url'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $this->configuration['user'] = $form_state->getValue('user');
      $this->configuration['token'] = $form_state->getValue('token');
      $this->configuration['redirect_url'] = $form_state->getValue('redirect_url');
      if ($form_state->getValue('password')) {
        // Override only in case empty value.
        $this->configuration['password'] = $form_state->getValue('password');
      }
      parent::submitConfigurationForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDataForApp():array {
    $data = parent::getDataForApp();
    $data['token'] = $this->configuration['token'];
    $data['redirect_url'] = $this->configuration['redirect_url'];
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getLoginForm() {
    return \Drupal::formBuilder()->getForm('Drupal\openy_gc_auth_example\Form\VirtualYExampleLoginForm');
  }

}
