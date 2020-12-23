<?php

namespace Drupal\openy_gc_auth_yusa\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\openy_gc_auth\GCUserAuthorizer;
use Drupal\openy_gc_auth_yusa\YUSAClientService;
use Drupal\user\Entity\User;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class VirtualYUSALoginForm provides Y-USA login form.
 *
 * @package Drupal\openy_gc_auth_yusa\Form
 */
class VirtualYUSALoginForm extends FormBase {

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
   * Private storage.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $privateTempStore;

  /**
   * Http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * The Gated Content User Authorizer.
   *
   * @var \Drupal\openy_gc_auth\GCUserAuthorizer
   */
  protected $gcUserAuthorizer;

  /**
   * YUSAClientService instance.
   *
   * @var \Drupal\openy_gc_auth_yusa\YUSAClientService
   */
  protected $yusaClient;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    RequestStack $requestStack,
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    MailManagerInterface $mail_manager,
    FloodInterface $flood,
    PrivateTempStoreFactory $private_temp_store,
    Client $client,
    GCUserAuthorizer $gcUserAuthorizer,
    YUSAClientService $yusaClientService
  ) {
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->mailManager = $mail_manager;
    $this->flood = $flood;
    $this->privateTempStore = $private_temp_store->get('openy_gc_auth.provider.yusa');
    $this->client = $client;
    $this->gcUserAuthorizer = $gcUserAuthorizer;
    $this->yusaClient = $yusaClientService;
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
      $container->get('flood'),
      $container->get('tempstore.private'),
      $container->get('http_client'),
      $container->get('openy_gc_auth.user_authorizer'),
      $container->get('openy_gc_auth_yusa_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openy_gc_auth_yusa_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $provider_config = $this->configFactory->get('openy_gc_auth.provider.yusa');

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

    $form['verification_id'] = [
      '#type' => 'textfield',
      '#title' => $provider_config->get('id_field_text'),
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

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Enter Virtual Y'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $provider_config = $this->configFactory->get('openy_gc_auth.provider.yusa');
    $id = $form_state->getValue('verification_id');
    $result = $this->yusaClient->getUserData($id);
    // Proceed only if user is Active.
    if (isset($result['Status']) && $result['Status'] == 'Active') {
      // 1. Case when user has no email but First Name and Last Name.
      if (
        !empty($result['FirstName']) &&
        !empty($result['LastName']) &&
        empty($result['Email'])
      ) {
        $name = $result['FirstName'] . ' ' . $result['LastName'] . ' ' . $id;
        $email = 'y-usa+' . $id . '@virtualy.org';
      }
      // 2. Case when user has no Email, First Name and Last Name.
      if (
        empty($result['FirstName']) &&
        empty($result['LastName']) &&
        empty($result['Email'])
      ) {
        $name = 'y-usa+' . $id;
        $email = $name . '@virtualy.org';
      }
      // 3. Case when user has Email, First Name and Last Name.
      if (
        !empty($result['FirstName']) &&
        !empty($result['LastName']) &&
        !empty($result['Email'])
      ) {
        $name = $result['FirstName'] . ' ' . $result['LastName'];
        $email = $result['Email'];
      }
      if (!empty($name) && !empty($email)) {
        // Check if user already created.
        $users = $this->entityTypeManager
          ->getStorage('user')
          ->loadByProperties(['mail' => $email]);
        $user = reset($users);

        // Create a new user in DB.
        if (!$user) {
          $active = TRUE;
          $user = $this->gcUserAuthorizer->createUser($name, $email, $active);
        }

        if ($user instanceof User) {
          if ($provider_config->get('enable_email_verification')) {
            $this->sendEmailVerification($user, $provider_config, $email);
            $form_state->setValue('verified', TRUE);
            $form_state->setRebuild(TRUE);
            return;
          }
          else {
            // Authorize user (register, login, log, etc).
            $this->gcUserAuthorizer->authorizeUser($name, $email, $result);
          }
        }
      }
    }
    else {
      if (isset($result['Status']) && $result['Status'] == 'Inactive') {
        $user_inactive_message = $this->configFactory->get('openy_gc_auth.provider.yusa')->get('user_inactive_message');
        $message = !empty($user_inactive_message) ? $user_inactive_message : $this->t('User is Inactive.');
        $this->messenger()->addError($message);
      }
      else {
        $this->messenger()->addError($this->t('Something went wrong. Please try again.'));
      }
    }

  }

  /**
   * Helper function for verification email sending.
   */
  protected function sendEmailVerification($user, $provider_config, $mail) {
    // Due to form rebuild we have double submit, to avoid double email send
    // we use tempstore to determinate that email already sent.
    if ($this->privateTempStore->get($mail)) {
      // Email already sent, clear tempstore.
      $this->privateTempStore->delete($mail);
      return;
    }

    $path = str_replace('user/reset', 'yusa-user/verification', user_pass_reset_url($user));
    $params = [
      'message' => $provider_config->get('email_verification_text') . '<br>',
    ];
    $params['message'] .= 'Click to verify your email: ' . $path;
    $this->mailManager->mail('openy_gc_auth_yusa', 'openy_gc_auth_yusa_email_verification', $mail, 'en', $params, NULL, TRUE);
    $this->privateTempStore->set($mail, TRUE);
  }

}
