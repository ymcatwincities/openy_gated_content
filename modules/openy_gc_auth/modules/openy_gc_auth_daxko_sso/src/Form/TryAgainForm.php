<?php

namespace Drupal\openy_gc_auth_daxko_sso\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Form for try again step in case of failed Daxko SSO login.
 */
class TryAgainForm extends FormBase {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $currentRequest;

  /**
   * ConfigFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    RequestStack $requestStack,
    ConfigFactoryInterface $config_factory
  ) {
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->configFactory = $config_factory;
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
    return 'openy_gc_auth_daxko_sso_try_again';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    if (!empty($this->currentRequest->query->get('error'))) {
      $form['error'] = [
        '#markup' => '<h4 class="alert alert-danger text-center">' . $this->t('There may be a problem with your account') . '</h4>',
      ];

      $form['error_contact_message'] = [
        '#markup' => '<div class="alert alert-info text-center">' . $this->configFactory->get('openy_gc_auth.provider.daxko_sso')
          ->get('error_accompanying_message') . '</div>',
      ];
    }

    $form['#action'] = $this->configFactory->get('openy_gated_content.settings')->get('virtual_y_login_url');

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Try Again'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->currentRequest->query->remove('error');
    $this->currentRequest->overrideGlobals();
  }

}
