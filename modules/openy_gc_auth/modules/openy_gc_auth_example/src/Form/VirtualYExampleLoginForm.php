<?php

namespace Drupal\openy_gc_auth_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\openy_gc_auth\GCUserAuthorizer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class VirtualYExampleLogin Form.
 *
 * @package Drupal\openy_gc_auth_example\Form
 */
class VirtualYExampleLoginForm extends FormBase {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $currentRequest;

  /**
   * The Gated Content User Authorizer.
   *
   * @var \Drupal\openy_gc_auth\GCUserAuthorizer
   */
  protected $gcUserAuthorizer;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    RequestStack $requestStack,
    GCUserAuthorizer $gcUserAuthorizer
  ) {
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->gcUserAuthorizer = $gcUserAuthorizer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('openy_gc_auth.user_authorizer')
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
    $name = 'dummy+' . $this->currentRequest->getClientIp() . '+' . rand(0, 10000);
    $email = $name . '@virtualy.org';
    // Authorize user (register, login, log, etc).
    $this->gcUserAuthorizer->authorizeUser($name, $email);
  }

}
