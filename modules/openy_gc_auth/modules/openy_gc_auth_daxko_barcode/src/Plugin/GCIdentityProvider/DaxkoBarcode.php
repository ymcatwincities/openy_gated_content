<?php

namespace Drupal\openy_gc_auth_daxko_barcode\Plugin\GCIdentityProvider;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\openy_gc_auth\GCIdentityProviderPluginBase;

/**
 * Daxko Barcode identity provider plugin.
 *
 * @GCIdentityProvider(
 *   id="daxkobarcode",
 *   label = @Translation("Daxko barcode provider"),
 *   config="openy_gc_auth.provider.daxco_barcode"
 * )
 */
class DaxkoBarcode extends GCIdentityProviderPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration():array {
    return [
      'enable_recaptcha' => TRUE,
      'secret' => '',
      'action_url' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['intro'] = [
      '#type' => 'item',
      '#description' => $this->t('This provider integrates with the Daxko "Virtual Areas" functionality. Please contact your Daxko support representative for documentation. After you have set up a Virtual Area, follow the instructions for a "Custom Integration".'),
    ];

    $form['enable_recaptcha'] = [
      '#title' => $this->t('Enable ReCaptcha'),
      '#description' => $this->t('Set to TRUE if you want ReCaptcha validation on login form. It is recommended that you leave this on.'),
      '#type' => 'checkbox',
      '#default_value' => $config['enable_recaptcha'],
    ];

    $form['secret'] = [
      '#title' => $this->t('Validation secret'),
      '#description' => $this->t('The validation secret provided by Daxko.'),
      '#type' => 'textfield',
      '#default_value' => $config['secret'],
      '#required' => TRUE,
    ];

    $form['action_url'] = [
      '#title' => $this->t('Action url'),
      '#description' => $this->t('The url provided in the Daxko <code>&lt;form&gt;</code> code. Only include the text between the quotes in <code>action="https://..."</code>'),
      '#type' => 'textfield',
      '#default_value' => $config['action_url'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state)
  {
    // Ensure ReCaptcha is set up before requiring it on our form.
    $enable_recaptcha = $form_state->getValue('enable_recaptcha');
    $recaptcha_site_key = \Drupal::config('recaptcha.settings')->get('site_key');

    if ($enable_recaptcha && !isset($recaptcha_site_key)) {
      $recaptcha_link = Link::createFromRoute('configure ReCaptcha', 'recaptcha.admin_settings_form')
        ->toString();
      $form_state
        ->setErrorByName('enable_recaptcha', $this
        ->t('You must @configure_recaptcha before enabling it on this form.', [
          '@configure_recaptcha' => $recaptcha_link
        ]));
    }

    parent::validateConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $this->configuration['enable_recaptcha'] = $form_state->getValue('enable_recaptcha');
      $this->configuration['secret'] = $form_state->getValue('secret');
      $this->configuration['action_url'] = $form_state->getValue('action_url');

      parent::submitConfigurationForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDataForApp():array {
    $data = parent::getDataForApp();
    $data['enableRecaptcha'] = (bool) $this->configuration['enable_recaptcha'];
    $data['action_url'] = $this->configuration['action_url'];
    $this->configFactory->get('recaptcha.settings')->get('site_key');
    $data['reCaptchaKey'] = $this->configFactory
      ->get('recaptcha.settings')
      ->get('site_key');
    return $data;
  }

}
