<?php

namespace Drupal\openy_gc_auth_custom\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class VirtualYCustomLoginForm.
 *
 * @package Drupal\openy_gc_auth_custom\Form
 */
class VirtualYCustomLoginForm extends FormBase {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $currentRequest;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type targeted by this resource.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityTypeManager;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The flood service.
   *
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $flood;

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestStack $requestStack, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, MailManagerInterface $mail_manager, FloodInterface $flood) {
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->mailManager = $mail_manager;
    $this->flood = $flood;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.mail'),
      $container->get('flood')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openy_gc_auth_custom_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $provider_config = $this->configFactory->get('openy_gc_auth.provider.custom');
    $form['#prefix'] = '<div id="form-wrapper">';
    $form['#suffix'] = '</div>';
    $form['#attributes'] = [
      'class' => ['container', 'mb-4'],
    ];

    if ($form_state->getValue('verified', FALSE)) {
      $link = Url::fromRoute(
        '<current>',
        [],
        ['absolute' => TRUE])->toString();
      $form['message'] = [
        '#markup' => $provider_config->get('verification_message') . "<br> <a href='$link'>Back to Login form</a>",
        '#prefix' => '<div class="alert alert-info">',
        '#suffix' => '</div>',
      ];
      return $form;
    }

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#size' => 35,
      '#required' => TRUE,
    ];

    if ($provider_config->get('enable_recaptcha')) {
      $form['captcha'] = [
        '#type' => 'captcha',
        '#captcha_type' => 'recaptcha/reCAPTCHA',
        '#captcha_validate' => 'recaptcha_captcha_validation',
      ];
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Enter Virtual Y'),
      '#ajax' => [
        'callback' => '::submitAjax',
        'wrapper' => 'form-wrapper',
        'effect' => 'fade',
        'progress' => [
          'type' => 'fullscreen',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $flood_config = $this->configFactory->get('user.flood');
    if (!$this->flood->isAllowed('openy_gc_auth_custom.login', $flood_config->get('user_limit'), $flood_config->get('user_window'))) {
      $form_state->setErrorByName('email', $this->t('Too many login requests from your IP address. It is temporarily blocked. Try again later or contact the site administrator.'));
      return;
    }

    $errors = $form_state->getErrors();
    if (!empty($errors)) {
      // Skip user existing check if we already have errors.
      return;
    }
    $users = $this->entityTypeManager
      ->getStorage('user')
      ->loadByProperties(['mail' => $form_state->getValue('email')]);

    if (empty($users)) {
      $form_state->setErrorByName('email', $this->t('User @mail not found.', [
        '@mail' => $form_state->getValue('email'),
      ]));
      $this->flood->register('openy_gc_auth_custom.login', $flood_config->get('user_window'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitAjax(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $provider_config = $this->configFactory->get('openy_gc_auth.provider.custom');
    $mail = $form_state->getValue('email');
    $users = $this->entityTypeManager
      ->getStorage('user')
      ->loadByProperties(['mail' => $mail]);
    $user = reset($users);
    if (!$user->isActive()) {
      if ($provider_config->get('enable_email_verification') && !$form_state->getValue('verified', FALSE)) {
        $this->sendEmailVerification($user, $provider_config, $mail);
        $form_state->setValue('verified', TRUE);
        $form_state->setRebuild(TRUE);
        return;
      }
      else {
        $user->setPassword(user_password());
        $user->activate();
        $user->save();
      }
    }

    user_login_finalize($user);
    $vy_settings = $this->configFactory->get('openy_gated_content.settings');
    $form_state->setResponse(new RedirectResponse($vy_settings->get('virtual_y_url'), 302));
  }

  /**
   * Helper function for verification email sending.
   */
  protected function sendEmailVerification($user, $provider_config, $mail) {
    $path = str_replace('user/reset', 'vy-user/verification', user_pass_reset_url($user));
    $params = [
      'message' => $provider_config->get('email_verification_text') . '<br>',
    ];
    $params['message'] .= 'Click to verify your email: ' . $path;
    $this->mailManager->mail('openy_gc_auth_custom', 'email_verification', $mail, 'en', $params, NULL, TRUE);
  }

}
