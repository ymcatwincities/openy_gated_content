<?php

namespace Drupal\openy_gc_auth_personify\Plugin\GCIdentityProvider;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\openy_gc_auth\GCIdentityProviderPluginBase;
use Drupal\personify\PersonifyClient;
use Drupal\personify\PersonifySSO;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Personify SSO identity provider plugin.
 *
 * @GCIdentityProvider(
 *   id="personify",
 *   label = @Translation("Personify provider"),
 *   config="openy_gc_auth.provider.personify"
 * )
 */
class Personify extends GCIdentityProviderPluginBase {

  /**
   * Personify SSO service.
   *
   * @var \Drupal\personify\PersonifySSO
   */
  protected $personifySSO;

  /**
   * Personify Client service.
   *
   * @var \Drupal\personify\PersonifyClient
   */
  protected $personifyClient;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config,
    EntityTypeManagerInterface $entity_type_manager,
    MessengerInterface $messenger,
    PersonifySSO $personifySSO,
    PersonifyClient $personifyClient
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config, $entity_type_manager);
    $this->messenger = $messenger;
    $this->personifySSO = $personifySSO;
    $this->personifyClient = $personifyClient;
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
      $container->get('messenger'),
      $container->get('personify.sso_client'),
      $container->get('personify.client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration():array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form = parent::buildConfigurationForm($form, $form_state);

    $personifyConfigForm = Link::createFromRoute($this->t('Personify configuration form'),
      'personify.settings', [], ['target' => '_blank'])->toString();
    $readMeUrl = Link::createFromRoute($this->t('README file'),
      'help.page',
      ['name' => 'openy_gc_auth_personify'],
      ['attributes' => ['target' => '_blank']]
    )->toString();

    // Check settings.
    $personifySettings = $this->configFactory->get('personify.settings');
    $env = $personifySettings->get('environment');
    $configLoginUrl = $this->configFactory->get('openy_gc_auth_personify.settings')->get($env . '_url_login');
    $wsdl = $personifySettings->get($env . '_wsdl');
    $endpoint = $personifySettings->get($env . '_endpoint');
    if (empty($env) || empty($configLoginUrl) || empty($wsdl) || empty($endpoint)) {
      $this->messenger->addError($this->t('You have to add all configs to settings.php based on @readme.', ['@readme' => $readMeUrl]));
    }

    $form['help'] = [
      '#type' => 'container',
      'text' => [
        '#theme' => 'item_list',
        '#items' => [
          $this->t('Setup Personify credentials in settings.php based on @readme.', ['@readme' => $readMeUrl]),
          $this->t('Select environment on @form.', ['@form' => $personifyConfigForm]),
        ],
      ],
    ];

    $form['error_accompanying_message'] = [
      '#title' => $this->t('Authentication error message'),
      '#description' => $this->t('Message displayed to user when he failed to log in using personify plugin.'),
      '#type' => 'textfield',
      '#default_value' => $config['error_accompanying_message'],
      '#required' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $this->configuration['error_accompanying_message'] = $form_state->getValue('error_accompanying_message');
      parent::submitConfigurationForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDataForApp():array {
    $data = parent::getDataForApp();

    $data['api_login_check'] = Url::fromRoute('openy_gc_auth_personify.personify_check')->toString();
    $data['api_auth'] = Url::fromRoute('openy_gc_auth_personify.personify_auth')->toString();
    $data['api_logout'] = Url::fromRoute('openy_gc_auth_personify.personify_logout')->toString();
    $data['error_accompanying_message'] = $this->configuration['error_accompanying_message'];
    return $data;
  }

  /**
   * Create Personify login URL.
   *
   * @param string $applicationUrl
   *   Application url.
   *
   * @return string|null
   *   Personify login URL.
   */
  public function getPersonifyLoginUrl($applicationUrl) {
    $options = [
      'absolute' => TRUE,
      'query' => [
        'dest' => urlencode($applicationUrl),
      ],
    ];

    // Generate auth URL that would base of validation token.
    $url = Url::fromRoute('openy_gc_auth_personify.personify_auth', [], $options)->toString();

    $vendor_token = $this->personifySSO->getVendorToken($url);
    $options = [
      'query' => [
        'vi' => $this->personifySSO->getConfigVendorId(),
        'vt' => $vendor_token,
      ],
    ];

    $env = $this->configFactory->get('personify.settings')->get('environment');
    $configLoginUrl = $this->configFactory->get('openy_gc_auth_personify.settings')->get($env . '_url_login');
    if (empty($configLoginUrl)) {
      $this->messenger->addWarning('Please, check Personify configs in settings.php.');
      return NULL;
    }
    $loginUrl = Url::fromUri($configLoginUrl, $options)->toString();

    return $loginUrl;
  }

  /**
   * {@inheritdoc}
   */
  public function getLoginForm() {
    return \Drupal::formBuilder()->getForm('Drupal\openy_gc_auth_personify\Form\VirtualYPersonifyLoginForm');
  }

}
