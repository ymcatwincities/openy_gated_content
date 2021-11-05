<?php

namespace Drupal\openy_gc_auth_personify\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class VirtualYPersonifyTryAgain Form.
 *
 * @package Drupal\openy_gc_personify\Form
 */
class VirtualYPersonifyTryAgainForm extends FormBase {

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
    return 'openy_gc_auth_example_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#prefix'] = '<h4 class="text-center">' . $this->t('You were not able to login') . '</h4>';

    $form['submit'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute('openy_gc_auth_personify.personify_check'),
      '#title' => $this->t('Try again'),
      '#attributes' => [
        'class' => [
          'gc-button',
        ],
      ],
    ];

    $form['#attributes'] = [
      'class' => 'text-center',
    ];

    $form['#suffix'] = '<div class="alert alert-info text-center">' . $this->configFactory->get('openy_gc_auth.provider.personify')->get('error_accompanying_message') . '</div>';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->currentRequest->query->remove('personify-error');
    $this->currentRequest->overrideGlobals();
  }

}
