<?php

namespace Drupal\openy_gc_auth_daxko_barcode\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class VirtualYDaxkoBarcodeLoginForm.
 *
 * @package Drupal\openy_gc_auth_daxko_barcode\Form
 */

/**
 * Form handler for VirtualY Daxko Barcode Login.
 */
class VirtualYDaxkoBarcodeLoginForm extends FormBase {

  /**
   * A request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $currentRequest;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestStack $requestStack, ConfigFactoryInterface $configFactory) {
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openy_gc_auth_daxko_barcode_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->get('openy_gc_auth.provider.daxko_barcode');

    $form['barcode'] = [
      '#title' => $config->get('form_label'),
      '#description' => $config->get('form_description'),
      '#type' => 'textfield',
      '#default_value' => '',
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Enter Virtual Y'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirectUrl(Url::fromRoute('openy_gc_auth_daxko_barcode.validate', ['barcode' => $form_state->getValue('barcode')]));
  }

}
