<?php

namespace Drupal\openy_gc_auth_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\user\Entity\User;

/**
 * Class VirtualYExampleLoginForm.
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
    $name = 'dummy+' . $this->currentRequest->getClientIp() . '+' . rand(0, 10000);
    $email = $name . '@virtualy.org';
    $user = User::create();
    $user->setPassword(user_password());
    $user->enforceIsNew();
    $user->setEmail($email);
    $user->setUsername($name);
    $user->addRole('virtual_y');
    $user->activate();
    $result = $account = $user->save();
    if ($result) {
      // We must load account because user has not id at save point.
      $account = user_load_by_name($name);
      user_login_finalize($account);
    }
  }

}
