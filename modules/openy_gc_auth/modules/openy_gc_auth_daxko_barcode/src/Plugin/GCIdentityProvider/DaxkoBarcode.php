<?php

namespace Drupal\openy_gc_auth_daxko_barcode\Plugin\GCIdentityProvider;

use Drupal\Core\Form\FormStateInterface;
use Drupal\openy_gc_auth\GCIdentityProviderPluginBase;

/**
 * Daxko Barcode identity provider plugin.
 *
 * @GCIdentityProvider(
 *   id="daxkobarcode",
 *   label = @Translation("Daxko barcode provider"),
 *   config="openy_gc_auth.provider.daxko_barcode"
 * )
 */
class DaxkoBarcode extends GCIdentityProviderPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration():array {
    return [
      'secret' => '',
      'action_url' => 'https://operations.daxko.com/online/XXXX/checkin/submit?area_id=YYYY',
      'form_label' => 'Barcode',
      'form_description' => 'This is the number found on your ID card.',
      'message_not_found' => 'That barcode was not found.',
      'message_access_denied' => 'That barcode does not have access to virtual content.',
      'message_duplicate_barcode' => 'That barcode is assigned to multiple members.',
      'message_invalid' => 'Something went wrong.',
      'message_help' => 'If you need assistance, please contact us at 555-555-5555 or email help@exampleymca.org.',
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

    $form['secret'] = [
      '#title' => $this->t('Validation secret'),
      '#description' => $this->t('The validation secret provided by Daxko.'),
      '#type' => 'textfield',
      '#default_value' => $config['secret'],
      '#required' => TRUE,
    ];

    $form['action_url'] = [
      '#title' => $this->t('Action url'),
      '#description' => $this->t('The url provided by Daxko. This should look like: <code>https://operations.daxko.com/online/XXXX/checkin/submit?area_id=YYYY</code>. You will find  <code>XXXX</code> and <code>YYYY</code> in the "Integration URL" on the "Edit Virtual Area" configuration page.'),
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

    $form['message_help'] = [
      '#title' => $this->t('Message for login failures'),
      '#description' => $this->t('Where should users go for help? If set this will be displayed after all login failure messages.'),
      '#type' => 'textfield',
      '#default_value' => $config['message_help'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $this->configuration['secret'] = $form_state->getValue('secret');
      $this->configuration['action_url'] = $form_state->getValue('action_url');
      $this->configuration['form_label'] = $form_state->getValue('form_label');
      $this->configuration['form_description'] = $form_state->getValue('form_description');
      $this->configuration['message_not_found'] = $form_state->getValue('message_not_found');
      $this->configuration['message_access_denied'] = $form_state->getValue('message_access_denied');
      $this->configuration['message_duplicate_barcode'] = $form_state->getValue('message_duplicate_barcode');
      $this->configuration['message_invalid'] = $form_state->getValue('message_invalid');
      $this->configuration['message_help'] = $form_state->getValue('message_help');

      parent::submitConfigurationForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getLoginForm() {
    return \Drupal::formBuilder()->getForm('Drupal\openy_gc_auth_daxko_barcode\Form\VirtualYDaxkoBarcodeLoginForm');
  }

}
