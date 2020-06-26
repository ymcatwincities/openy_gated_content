<?php

namespace Drupal\openy_gc_auth_custom\Plugin\GCIdentityProvider;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\openy_gc_auth\GCIdentityProviderPluginBase;

/**
 * Custom identity provider plugin.
 *
 * @GCIdentityProvider(
 *   id="custom",
 *   label = @Translation("Custom provider"),
 *   config="openy_gc_auth.provider.custom"
 * )
 */
class Custom extends GCIdentityProviderPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration():array {
    return [
      'enable_recaptcha' => TRUE,
      'api_endpoint' => '/openy-gc-auth/provider/custom/login',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['import'] = [
      '#title' => $this->t('Run users import'),
      '#type' => 'link',
      '#weight' => 0,
      '#url' => Url::fromRoute('openy_gc_auth_custom.import_csv'),
      '#attributes' => ['class' => ['button']],
    ];

    $form['enable_recaptcha'] = [
      '#title' => $this->t('Enable ReCaptcha'),
      '#description' => $this->t('Set to TRUE if you want ReCaptcha validation on login form.'),
      '#type' => 'checkbox',
      '#default_value' => $config['enable_recaptcha'],
    ];

    $form['api_endpoint'] = [
      '#title' => $this->t('API endpoint'),
      '#description' => $this->t('Change this value only in case you have custom endpoint for this.'),
      '#type' => 'textfield',
      '#default_value' => $config['api_endpoint'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $this->configuration['enable_recaptcha'] = $form_state->getValue('enable_recaptcha');
      $this->configuration['api_endpoint'] = $form_state->getValue('api_endpoint');
      parent::submitConfigurationForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDataForApp():array {
    $data = parent::getDataForApp();
    $data['enableRecaptcha'] = (bool) $this->configuration['enable_recaptcha'];
    $data['apiEndpoint'] = $this->configuration['api_endpoint'];
    $this->configFactory->get('recaptcha.settings')->get('site_key');
    $data['reCaptchaKey'] = $this->configFactory
      ->get('recaptcha.settings')
      ->get('site_key');

    return $data;
  }

}
