<?php

namespace Drupal\openy_gc_auth_custom\Plugin\GCIdentityProvider;

use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\openy_gated_content\GCUserService;
use Drupal\openy_gc_auth\GCIdentityProviderPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom identity provider plugin.
 *
 * @GCIdentityProvider(
 *   id="custom",
 *   label = @Translation("Custom provider"),
 *   config="openy_gc_auth.provider.custom"
 * )
 */
class Custom extends GCIdentityProviderPluginBase {

  const DEFAULT_LINK_LIFE_TIME = 14400;

  /**
   * The email validator.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config,
    EntityTypeManagerInterface $entity_type_manager,
    FormBuilderInterface $form_builder,
    GCUserService $gc_user_service,
    EmailValidatorInterface $email_validator
  ) {

    parent::__construct($configuration, $plugin_id, $plugin_definition, $config, $entity_type_manager, $form_builder, $gc_user_service);
    $this->emailValidator = $email_validator;
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
      $container->get('form_builder'),
      $container->get('openy_gated_content.user_service'),
      $container->get('email.validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration():array {
    return [
      'enable_recaptcha' => TRUE,
      'enable_email_verification' => TRUE,
      'require_email_verification' => FALSE,
      'email_verification_link_life_time' => self::DEFAULT_LINK_LIFE_TIME,
      'email_verification_text' => 'Hello! <br> You’re just one step away from accessing your Virtual YMCA. Please open the link below to begin enjoying YMCA content made exclusively for members like you.',
      'verification_message' => 'We have sent a verification link to the email address you provided. Please open this link and activate your account. If you do not receive an email, please try again or contact us at XXX-XXX-XXXX to ensure we have the correct email on file for your membership.',
      'one_time_link_invalid_message' => 'You have tried to use a one-time login link that has either been used or is no longer valid. Please request a new one using the form below.',
      'exclude_users' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['enable_recaptcha'] = [
      '#title' => $this->t('Enable ReCaptcha'),
      '#description' => $this->t('Set to TRUE if you want ReCaptcha validation on login form.'),
      '#type' => 'checkbox',
      '#default_value' => $config['enable_recaptcha'],
    ];

    $form['verification'] = [
      '#type' => 'details',
      '#title' => $this->t('Email verification'),
      '#open' => FALSE,
    ];

    $form['verification']['enable_email_verification'] = [
      '#title' => $this->t('Enable Email verification'),
      '#description' => $this->t('Set to TRUE if you want enable one-time login link sending to user email for verification.'),
      '#type' => 'checkbox',
      '#default_value' => $config['enable_email_verification'],
      '#attributes' => [
        'name' => 'enable_email_verification',
      ],
    ];

    $form['verification']['require_email_verification'] = [
      '#title' => $this->t('Require Email verification'),
      '#description' => $this->t('Set to TRUE if you want to use email verification on user login using a new browser. If FALSE - email verification will be used only on first login for account activation.'),
      '#type' => 'checkbox',
      '#default_value' => $config['require_email_verification'],
      '#states' => [
        'visible' => [
          ':input[name="enable_email_verification"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['verification']['email_verification_link_life_time'] = [
      '#type' => 'select',
      '#title' => $this->t('Login link life time'),
      '#description' => $this->t('Select the time period after which the verification link will expire.'),
      '#default_value' => $config['email_verification_link_life_time'],
      '#required' => TRUE,
      '#options' => [
        3600 => $this->t('1 hour'),
        7200 => $this->t('2 hours'),
        10800 => $this->t('3 hours'),
        14400 => $this->t('4 hours'),
        18000 => $this->t('5 hours'),
        86400 => $this->t('1 day'),
        172800 => $this->t('2 days'),
      ],
    ];

    $form['verification']['email_verification_text'] = [
      '#title' => $this->t('Email verification text'),
      '#description' => $this->t('This text will be used in the email.'),
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#default_value' => $config['email_verification_text'],
      '#required' => TRUE,
    ];

    $form['verification']['verification_message'] = [
      '#title' => $this->t('Verification message'),
      '#description' => $this->t('This text will be displayed in the application after unverified user tried to login.'),
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#default_value' => $config['verification_message'],
      '#required' => TRUE,
    ];

    $form['verification']['one_time_link_invalid_message'] = [
      '#title' => $this->t('Message for One Time Link no valid'),
      '#description' => $this->t('This text will be displayed as error message when user was used a no valid  One time link.'),
      '#type' => 'textarea',
      '#default_value' => $config['one_time_link_invalid_message'],
      '#required' => TRUE,
    ];

    $form['migrate'] = [
      '#type' => 'details',
      '#title' => $this->t('Migration settings'),
      '#open' => FALSE,
    ];

    $form['migrate']['info'] = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => [
        $this->t('You can upload your CSV file on this @link.', [
          '@link' => Link::createFromRoute(
            $this->t('form'),
            'openy_gc_auth_custom.upload_csv',
            [],
            ['attributes' => ['target' => '_blank']])->toString(),
        ]),
        $this->t('You can execute migration on this @link.', [
          '@link' => Link::createFromRoute(
            $this->t('page'),
            'migrate_tools.execute',
            [
              'migration_group' => 'gc_auth',
              'migration' => 'gc_auth_custom_users',
            ],
            ['attributes' => ['target' => '_blank']])->toString(),
        ]),
      ],
    ];

    $form['migrate_custom'] = [
      '#type' => 'details',
      '#title' => $this->t('Migration users exclude'),
      '#open' => FALSE,
    ];

    $form['migrate_custom']['exclude_users'] = [
      '#title' => $this->t('Users exclude'),
      '#description' => $this->t('Add users emails that should be excluded from migration (One email per line).'),
      '#type' => 'textarea',
      '#default_value' => !empty($config['exclude_users']) ? implode(PHP_EOL, $config['exclude_users']) : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $emails_str = $form_state->getValue('exclude_users');
    if ($emails_str) {
      $emails = array_filter(explode(PHP_EOL, $emails_str));
      foreach ($emails as $email) {
        $clean_email = trim(preg_replace('/\s\s+/', '', $email));
        if (!$this->emailValidator->isValid($clean_email)) {
          $form_state->setErrorByName('exclude_users', $this->t('Email "@mail" invalid', [
            '@mail' => $clean_email,
          ]));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $this->configuration['enable_recaptcha'] = $form_state->getValue('enable_recaptcha');
      $this->configuration['api_endpoint'] = $form_state->getValue('api_endpoint');
      $this->configuration['enable_email_verification'] = $form_state->getValue('enable_email_verification');
      $this->configuration['require_email_verification'] = $form_state->getValue('require_email_verification');
      $this->configuration['email_verification_link_life_time'] = $form_state->getValue('email_verification_link_life_time');
      $this->configuration['email_verification_text'] = !empty($form_state->getValue('email_verification_text')) ? $form_state->getValue('email_verification_text')['value'] : '';
      $this->configuration['verification_message'] = !empty($form_state->getValue('verification_message')) ? $form_state->getValue('verification_message')['value'] : '';
      $this->configuration['one_time_link_invalid_message'] = !empty($form_state->getValue('one_time_link_invalid_message')) ? $form_state->getValue('one_time_link_invalid_message') : '';

      $emails_str = $form_state->getValue('exclude_users');
      if ($emails_str) {
        $emails = array_filter(explode(PHP_EOL, $emails_str));
        $emails_data = array_map(function ($email) {
          return trim(preg_replace('/\s\s+/', '', $email));
        }, $emails);
        $this->configuration['exclude_users'] = $emails_data;
      }
      else {
        $this->configuration['exclude_users'] = [];
      }

      parent::submitConfigurationForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getLoginForm() {
    return $this->formBuilder->getForm('Drupal\openy_gc_auth_custom\Form\VirtualYCustomLoginForm');
  }

}
