<?php

namespace Drupal\openy_gc_auth_daxko_barcode\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class VirtualYDaxkoBarcodeLoginForm.
 *
 * @package Drupal\openy_gc_auth_daxko_barcode\Form
 */
class VirtualYDaxkoBarcodeLoginForm extends FormBase {

  /**
   * A request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $currentRequest;

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestStack $requestStack) {
    $this->currentRequest = $requestStack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
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

    $form['barcode'] = [
      '#title' => $this->t('Barcode'),
      '#description' => '',
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
