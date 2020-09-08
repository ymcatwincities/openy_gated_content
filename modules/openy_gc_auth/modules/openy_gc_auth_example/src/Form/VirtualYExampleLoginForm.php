<?php

namespace Drupal\openy_gc_auth_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class VirtualYExampleLoginForm
 *
 * @package Drupal\openy_gc_auth_example\Form
 */
class VirtualYExampleLoginForm extends FormBase {

  /**
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
    return 'openy_gc_auth_example_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

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

    $email = 'dummy+' . $this->currentRequest->getClientIp() . '+' . rand(10000) . '@virtualy.org';
    //@TODO create user account and log in it.

  }

}
