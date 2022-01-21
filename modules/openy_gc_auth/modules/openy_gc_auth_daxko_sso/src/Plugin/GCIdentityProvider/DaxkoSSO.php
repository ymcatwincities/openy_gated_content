<?php

namespace Drupal\openy_gc_auth_daxko_sso\Plugin\GCIdentityProvider;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\daxko_sso\DaxkoSSOClient;
use Drupal\openy_gated_content\GCUserService;
use Drupal\openy_gc_auth\GCIdentityProviderPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Example identity provider plugin.
 *
 * @GCIdentityProvider(
 *   id="daxkosso",
 *   label = @Translation("Daxko SSO provider"),
 *   config="openy_gc_auth.provider.daxko_sso"
 * )
 */
class DaxkoSSO extends GCIdentityProviderPluginBase {

  use MessengerTrait;

  /**
   * Daxko Client service instance.
   *
   * @var \Drupal\daxko_sso\DaxkoSSOClient
   */
  protected $daxkoClient;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $request;

  /**
   * Daxko logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $daxkoLoggerChannel;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration():array {
    return [
      'redirect_url' => '',
      'login_mode' => 'present_login_button',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config,
    EntityTypeManagerInterface $entity_type_manager,
    DaxkoSSOClient $daxkoSSOClient,
    RequestStack $requestStack,
    FormBuilderInterface $form_builder,
    LoggerChannelInterface $daxko_logger_channel,
    GCUserService $gc_user_service
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config, $entity_type_manager, $form_builder, $gc_user_service);
    $this->daxkoClient = $daxkoSSOClient;
    $this->request = $requestStack->getCurrentRequest();
    $this->daxkoLoggerChannel = $daxko_logger_channel;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('daxko_sso.client'),
      $container->get('request_stack'),
      $container->get('form_builder'),
      $container->get('logger.factory')->get('daxko_sso'),
      $container->get('openy_gated_content.user_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['redirect_url'] = [
      '#title' => $this->t('Url'),
      '#description' => $this->t('Daxko back link redirect e.g. /openy-gc-auth-daxko-sso/back-redirect'),
      '#type' => 'textfield',
      '#default_value' => $config['redirect_url'],
      '#required' => TRUE,
    ];

    $form['error_accompanying_message'] = [
      '#title' => $this->t('Authentication error message'),
      '#description' => $this->t('Message displayed to user when he failed to log in using this plugin.'),
      '#type' => 'textfield',
      '#default_value' => $config['error_accompanying_message'],
      '#required' => FALSE,
    ];

    $form['login_mode'] = [
      '#title' => $this->t('Login mode'),
      '#description' => $this->t('When "Redirect immediately" mode used, it is not possible to redirect user after login to his initially requested VY route.'),
      '#type' => 'radios',
      '#default_value' => $config['login_mode'],
      '#required' => TRUE,
      '#options' => [
        'present_login_button' => $this->t('Present login button'),
        'redirect_immediately' => $this->t('Redirect immediately'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $this->configuration['redirect_url'] = $form_state->getValue('redirect_url');
      $this->configuration['error_accompanying_message'] = $form_state->getValue('error_accompanying_message');
      $this->configuration['login_mode'] = $form_state->getValue('login_mode');

      $baseUrl = $this->request->getSchemeAndHttpHost();

      $result = $this
        ->daxkoClient
        ->registerSSORedirectLink($baseUrl . $form_state->getValue('redirect_url'));

      if (!$result['error']) {
        $this->messenger()->addStatus('We were able to register your URL at Daxko API settings');
      }
      else {
        $this->messenger()->addError('Attempt to register redirect url was failed. ' . $result['message']);
      }
      parent::submitConfigurationForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getLoginForm() {

    if ($this->request->query->has('error')) {
      return $this->formBuilder->getForm('Drupal\openy_gc_auth_daxko_sso\Form\TryAgainForm');
    }
    elseif ($this->configuration['login_mode'] === 'present_login_button') {
      return $this->formBuilder->getForm('Drupal\openy_gc_auth_daxko_sso\Form\VirtualYDaxkoSSOLoginForm');
    }

    // Forcing no-cache at redirect headers.
    $headers = [
      'Cache-Control' => 'no-cache',
    ];
    $response = new RedirectResponse(
      Url::fromRoute('openy_gc_auth_daxko_sso.daxko_link_controller_hello')->toString(),
      302,
      $headers
    );
    $response->send();
  }

  /**
   * {@inheritdoc}
   */
  public function getMemberNotificationEmail(int $uid): string {
    /** @var \Drupal\user\UserInterface $user */
    $user = $this->userStorage->load($uid);
    $daxko_email = $user->getEmail();

    if (strpos($daxko_email, "daxko-") === FALSE) {
      $this->daxkoLoggerChannel->error("User with id: \"{$uid}\" was not created using Daxko SSO.");
      return '';
    }

    // Retrieve the member id from email, that has the following format:
    // "daxko-{$userData->member_id}@virtualy.openy.org".
    /* @see \Drupal\openy_gc_auth_daxko_sso\Controller\DaxkoLinkController::backlink() */
    $member_id = substr($daxko_email, 6, -19);

    if ($member_id === FALSE || empty($member_id)) {
      $this->daxkoLoggerChannel->error("There was an error extracting member id from email: \"{$daxko_email}\" of user with id: \"{$uid}\".");
      return '';
    }

    $user_data = $this->daxkoClient->getRequest("members/{$member_id}");
    // Taking the first email from the emails array.
    $first_email_item = reset($user_data->emails);

    if (empty($first_email_item) || !isset($first_email_item->email)) {
      $this->daxkoLoggerChannel->error(
        "Emails for user with member id \"{$member_id}\" received from Daxko are invalid: %emails.",
        ['%emails' => print_r($user_data->emails, TRUE)]
      );
      return '';
    }

    return $first_email_item->email;
  }

}
