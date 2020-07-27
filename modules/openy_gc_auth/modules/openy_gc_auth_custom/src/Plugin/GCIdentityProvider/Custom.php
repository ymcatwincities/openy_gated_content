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
      'enable_email_verification' => TRUE,
      'email_verification_api_endpoint' => '/openy-gc-auth/provider/custom/login-by-link',
      'email_verification_link_life_time' => 14400,
      'email_verification_text' => 'Hello! <br> You’re just one step away from accessing your Virtual YMCA. Please open the link below to begin enjoying YMCA content made exclusively for members like you.',
      'verification_message' => 'We have sent a verification link to the email address you provided. Please open this link and activate your account. If you do not receive an email, please try again or contact us at XXX-XXX-XXXX to ensure we have the correct email on file for your membership.',
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

    $form['users_list'] = [
      '#title' => $this->t('Users List'),
      '#type' => 'link',
      '#weight' => 0,
      '#url' => Url::fromUserInput('/admin/openy/virtual-ymca/gc-auth-settings/provider/custom/users'),
      '#attributes' => ['class' => ['button', 'button--primary']],
    ];

    $form['enable_recaptcha'] = [
      '#title' => $this->t('Enable ReCaptcha'),
      '#description' => $this->t('Set to TRUE if you want ReCaptcha validation on login form.'),
      '#type' => 'checkbox',
      '#default_value' => $config['enable_recaptcha'],
    ];

    $form['api_endpoint'] = [
      '#title' => $this->t('Login form API endpoint'),
      '#description' => $this->t('Change this value only in case you have custom endpoint for this.'),
      '#type' => 'textfield',
      '#default_value' => $config['api_endpoint'],
      '#required' => TRUE,
    ];

    $form['verification'] = [
      '#type' => 'details',
      '#title' => $this->t('Email verification'),
      '#open' => TRUE,
    ];

    $form['verification']['enable_email_verification'] = [
      '#title' => $this->t('Enable Email verification'),
      '#description' => $this->t('Set to TRUE if you want enable one-time login link sending to user email for verification.'),
      '#type' => 'checkbox',
      '#default_value' => $config['enable_email_verification'],
    ];

    $form['verification']['email_verification_api_endpoint'] = [
      '#title' => $this->t('Email verification API endpoint'),
      '#description' => $this->t('Change this value only in case you have custom endpoint for this.'),
      '#type' => 'textfield',
      '#default_value' => $config['email_verification_api_endpoint'],
      '#required' => TRUE,
    ];

    $form['verification']['email_verification_link_life_time'] = [
      '#type' => 'select',
      '#title' => $this->t('Login link life time'),
      '#description' => $this->t('Select the time period after which the verification link will expire.'),
      '#default_value' => $config['email_verification_link_life_time'],
      '#required' => TRUE,
      '#options' => [
        3600 => $this->t('1 hour'),
        7200 => $this->t('2 hours'),
        10800 => $this->t('3 hours'),
        14400 => $this->t('4 hours'),
        18000 => $this->t('5 hours'),
        86400 => $this->t('1 day'),
        172800 => $this->t('2 days'),
      ],
    ];

    $form['verification']['email_verification_text'] = [
      '#title' => $this->t('Email verification text'),
      '#description' => $this->t('This text will be used in the email.'),
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#default_value' => $config['email_verification_text'],
      '#required' => TRUE,
    ];

    $form['verification']['verification_message'] = [
      '#title' => $this->t('Verification message'),
      '#description' => $this->t('This text will be displayed in the application after unverified user tried to login.'),
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#default_value' => $config['verification_message'],
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
      $this->configuration['enable_email_verification'] = $form_state->getValue('enable_email_verification');
      $this->configuration['email_verification_api_endpoint'] = $form_state->getValue('email_verification_api_endpoint');
      $this->configuration['email_verification_link_life_time'] = $form_state->getValue('email_verification_link_life_time');
      // TODO: check that text_format works on submit.
      $this->configuration['email_verification_text'] = !empty($form_state->getValue('email_verification_text')) ? $form_state->getValue('email_verification_text')['value'] : '';
      $this->configuration['verification_message'] = !empty($form_state->getValue('verification_message')) ? $form_state->getValue('verification_message')['value'] : '';
      parent::submitConfigurationForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDataForApp():array {
    $data = parent::getDataForApp();
    $data['enableRecaptcha'] = (bool) $this->configuration['enable_recaptcha'];
    $data['emailVerification'] = (bool) $this->configuration['enable_email_verification'];
    $data['apiEndpoint'] = $this->configuration['api_endpoint'];
    $data['emailVerificationApiEndpoint'] = $this->configuration['email_verification_api_endpoint'];
    $this->configFactory->get('recaptcha.settings')->get('site_key');
    $data['reCaptchaKey'] = $this->configFactory
      ->get('recaptcha.settings')
      ->get('site_key');

    return $data;
  }

}
