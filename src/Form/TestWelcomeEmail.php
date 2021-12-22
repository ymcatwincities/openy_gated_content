<?php

namespace Drupal\openy_gated_content\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Utility\Token;
use Drupal\openy_gated_content\GCUserService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides form to send the test welcome message.
 */
class TestWelcomeEmail extends FormBase {

  /**
   * The Gated Content User Service.
   *
   * @var \Drupal\openy_gated_content\GCUserService
   */
  protected $gcUserService;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * {@inheritdoc}
   */
  public function __construct(GCUserService $gc_user_service, Token $token) {
    $this->gcUserService = $gc_user_service;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('openy_gated_content.user_service'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openy_gc_test_welcome_email';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('openy_gated_content.welcome_email_settings');
    $configs_map = [
      'email_subject' => $this->t('Email subject'),
      'email_body' => $this->t('Email body'),
    ];

    $form['notice'] = [
      '#type' => 'markup',
      '#markup' => $this->t('The welcome message will be sent to the email assigned to your account (<b>@mail</b>).', [
        '@mail' => $this->currentUser()->getEmail(),
      ]),
    ];

    foreach ($configs_map as $key => $label) {
      $value = $this->token->replace($config->get($key), ['user' => $this->currentUser()]);
      $form[$key] = [
        '#markup' => '<div class="form-item"><strong>' . $label . ':</strong><br/>' . $value . '</div>',
      ];
    }

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Send welcome message'),
        '#button_type' => 'primary',
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $user = $this->currentUser();
    $message = $this->gcUserService->sendEmail(
      'welcome_email',
      $user->getEmail(),
      ['user' => $user]
    );
    if (!empty($message['result']) && $message['result']) {
      $this->messenger()->addStatus($this->t('The message has been sent. Please, check it in your incoming mail.'));
    }
  }

}
