<?php

namespace Drupal\openy_gc_auth_daxko_barcode\Plugin\GCIdentityProvider;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
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
      'form_label' => 'Barcode',
      'form_description' => '',
      'message_not_found' => 'That barcode was not found.',
      'message_access_denied' => 'That barcode does not have access to virtual content.',
      'message_duplicate_barcode' => 'That barcode is assigned to multiple members.',
      'message_invalid' => 'Something went wrong. Please try again or contact us for assistance.'
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
      '#type' => 'url',
      '#default_value' => $config['action_url'],
      '#required' => TRUE,
    ];

    $form['form_label'] = [
      '#title' => $this->t('Form label'),
      '#description' => $this->t('The label of the field where the user will enter their barcode or check-in number.'),
      '#type' => 'textfield',
      '#default_value' => $config['form_label'],
      '#required' => TRUE,
    ];

    $form['form_description'] = [
      '#title' => $this->t('Form description'),
      '#description' => $this->t('Some short help text you can provide the user to find their barcode or check-in number. If you need more than a sentence or two of plain text, please add it above the login form.'),
      '#type' => 'textfield',
      '#default_value' => $config['form_description'],
    ];

    $form['message_not_found'] = [
      '#title' => $this->t('Message for "not found" status'),
      '#description' => $this->t('What the user should see when Daxko reports a "not found" status.'),
      '#type' => 'textfield',
      '#default_value' => $config['message_not_found'],
    ];

    $form['message_access_denied'] = [
      '#title' => $this->t('Message for "access denied" status'),
      '#description' => $this->t('What the user should see when Daxko reports a "access denied" status.'),
      '#type' => 'textfield',
      '#default_value' => $config['message_access_denied'],
    ];

    $form['message_duplicate_barcode'] = [
      '#title' => $this->t('Message for "duplicate barcode" status'),
      '#description' => $this->t('What the user should see when Daxko reports a "duplicate barcode" status.'),
      '#type' => 'textfield',
      '#default_value' => $config['message_duplicate_barcode'],
    ];

    $form['message_invalid'] = [
      '#title' => $this->t('Message for "invalid" status'),
      '#description' => $this->t('What the user should see when Daxko reports a "invalid" status.'),
      '#type' => 'textfield',
      '#default_value' => $config['message_invalid'],
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
      $this->configuration['form_label'] = $form_state->getValue('form_label');
      $this->configuration['form_description'] = $form_state->getValue('form_description');
      $this->configuration['message_not_found'] = $form_state->getValue('message_not_found');
      $this->configuration['message_access_denied'] = $form_state->getValue('message_access_denied');
      $this->configuration['message_duplicate_barcode'] = $form_state->getValue('message_duplicate_barcode');
      $this->configuration['message_invalid'] = $form_state->getValue('message_invalid');

      parent::submitConfigurationForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDataForApp():array {
    $data = parent::getDataForApp();
    $data['barcodeValidate'] = Url::fromRoute('openy_gc_auth_daxko_barcode.validate')->toString();
    $data['enableRecaptcha'] = (bool) $this->configuration['enable_recaptcha'];
    $data['formLabel'] = $this->configuration['form_label'];
    $data['formDescription'] = $this->configuration['form_description'];
    $this->configFactory->get('recaptcha.settings')->get('site_key');
    $data['reCaptchaKey'] = $this->configFactory
      ->get('recaptcha.settings')
      ->get('site_key');
    return $data;
  }

}
