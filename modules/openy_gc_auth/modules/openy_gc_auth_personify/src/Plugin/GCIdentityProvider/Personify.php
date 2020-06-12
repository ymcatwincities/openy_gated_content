<?php

namespace Drupal\openy_gc_auth_personify\Plugin\GCIdentityProvider;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\openy_gc_auth\GCIdentityProviderPluginBase;

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
   * {@inheritdoc}
   */
  public function defaultConfiguration():array {
    return [
      'api_login_check' => '/openy_gc_auth_personify/check/login',
      'api_get_login_url' => '/openy_gc_auth_personify/get-login-url',
      'api_auth' => '/openy_gc_auth_personify/auth',
      'api_logout' => '/openy_gc_auth_personify/logout',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form = parent::buildConfigurationForm($form, $form_state);

    $personifyConfigForm = Link::createFromRoute($this->t('Personify configuration form'),
      'personify.settings', [], ['target' => '_blank'])->toString();
    $readMeUrl = Link::fromTextAndUrl($this->t('README file'),
      Url::fromUserInput('/' . drupal_get_path('module', 'openy_gc_auth_personify') . '/README.md',
        ['attributes' => ['target' => '_blank']])
    )->toString();

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

    $form['endpoints'] = [
      '#type' => 'details',
      '#title' => t('Custom Personify endpoints'),
      '#collapsible' => TRUE,
      '#open' => FALSE,
      '#description' => $this->t('Change these values only in case you have custom endpoints for every URL.'),
    ];
    $form['endpoints']['api_login_check'] = [
      '#title' => $this->t('Login check endpoint'),
      '#description' => $this->t('Endpoint to check whether user is logged in and return user data to application.'),
      '#type' => 'textfield',
      '#default_value' => $config['api_login_check'],
      '#required' => TRUE,
    ];
    $form['endpoints']['api_get_login_url'] = [
      '#title' => $this->t('Create Personify URL endpoint'),
      '#description' => $this->t('Endpoint to create and return to application Personify login url.'),
      '#type' => 'textfield',
      '#default_value' => $config['api_get_login_url'],
      '#required' => TRUE,
    ];
    $form['endpoints']['api_auth'] = [
      '#title' => $this->t('Auth endpoint'),
      '#description' => $this->t('Endpoint to receive Personify redirect and authenticate user.'),
      '#type' => 'textfield',
      '#default_value' => $config['api_auth'],
      '#required' => TRUE,
    ];
    $form['endpoints']['api_logout'] = [
      '#title' => $this->t('Logout endpoint'),
      '#description' => $this->t('Endpoint to sign out user and return to application url to redirect after logout.'),
      '#type' => 'textfield',
      '#default_value' => $config['api_logout'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $this->configuration['api_login_check'] = $form_state->getValue('api_login_check');
      $this->configuration['api_get_login_url'] = $form_state->getValue('api_get_login_url');
      $this->configuration['api_auth'] = $form_state->getValue('api_auth');
      $this->configuration['api_logout'] = $form_state->getValue('api_logout');
      parent::submitConfigurationForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDataForApp():array {
    $data = parent::getDataForApp();

    $data['api_login_check'] = $this->configuration['api_login_check'];
    $data['api_get_login_url'] = $this->configuration['api_get_login_url'];
    $data['api_auth'] = $this->configuration['api_auth'];
    $data['api_logout'] = $this->configuration['api_logout'];
    return $data;
  }

}
