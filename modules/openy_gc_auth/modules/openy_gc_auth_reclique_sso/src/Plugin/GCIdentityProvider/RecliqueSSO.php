<?php

namespace Drupal\openy_gc_auth_reclique_sso\Plugin\GCIdentityProvider;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\openy_gated_content\GCUserService;
use Drupal\openy_gc_auth\GCIdentityProviderPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Identity provider plugin.
 *
 * @GCIdentityProvider(
 *   id="reclique_sso",
 *   label = @Translation("Reclique SSO OAuth2 provider"),
 *   config="openy_gc_auth.provider.reclique_sso"
 * )
 */
class RecliqueSSO extends GCIdentityProviderPluginBase {

  use MessengerTrait;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $request;

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config,
    EntityTypeManagerInterface $entity_type_manager,
    RequestStack $requestStack,
    FormBuilderInterface $form_builder,
    GCUserService $gc_user_service
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config, $entity_type_manager, $form_builder, $gc_user_service);
    $this->request = $requestStack->getCurrentRequest();
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
      $container->get('request_stack'),
      $container->get('form_builder'),
      $container->get('openy_gated_content.user_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'authorization_server' => 'https://[association_slug].recliquecore.com',
      'login_mode' => 'present_login_button',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['authorization_server'] = [
      '#type' => 'url',
      '#title' => $this->t('Authorization server'),
      '#default_value' => $config['authorization_server'],
      '#description' => $this->t('It is most likely "https://[association_slug].recliquecore.com", where association_slug should be provided from Reclique.'),
    ];

    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Id'),
      '#default_value' => $config['client_id'],
      '#description' => $this->t('Your Reclique client id.'),
    ];

    $form['client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret'),
      '#default_value' => $config['client_secret'],
      '#description' => $this->t('Your Reclique client secret.'),
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
      $this->configuration['authorization_server'] = $form_state->getValue('authorization_server');
      $this->configuration['client_id'] = $form_state->getValue('client_id');
      $this->configuration['client_secret'] = $form_state->getValue('client_secret');
      $this->configuration['error_accompanying_message'] = $form_state->getValue('error_accompanying_message');
      $this->configuration['login_mode'] = $form_state->getValue('login_mode');

      parent::submitConfigurationForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getLoginForm() {
    if ($this->request->query->has('error')) {
      return $this->formBuilder->getForm('Drupal\openy_gc_auth_reclique_sso\Form\TryAgainForm');
    }

    if ($this->configuration['login_mode'] === 'present_login_button') {
      return $this->formBuilder->getForm('Drupal\openy_gc_auth_reclique_sso\Form\ContinueWithRecliqueLoginForm');
    }

    // Forcing no-cache at redirect headers.
    $headers = [
      'Cache-Control' => 'no-cache',
    ];
    $response = new RedirectResponse(
      Url::fromRoute('openy_gc_auth_reclique_sso.authenticate_redirect')
        ->toString(),
      302,
      $headers
    );

    return $response->send();
  }

}
