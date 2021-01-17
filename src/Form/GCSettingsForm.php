<?php

namespace Drupal\openy_gated_content\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Identity Provider Edit Form.
 */
class GCSettingsForm extends ConfigFormBase {

  const PAGER_LIMIT_DEFAULT = 12;

  /**
   * The Identity Provider plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $identityProviderManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(PluginManagerInterface $gc_identity_provider_manager) {
    $this->identityProviderManager = $gc_identity_provider_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.gc_identity_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['openy_gated_content.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openy_gc_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('openy_gated_content.settings');
    $form['#tree'] = TRUE;

    $form['app_settings'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Virtual YMCA APP settings'),
    ];

    $form['app_settings']['event_add_to_calendar'] = [
      '#title' => $this->t('Add to Calendar'),
      '#description' => $this->t('Enable/Disable "Add to Calendar" feature for events.'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('event_add_to_calendar') ?? FALSE,
    ];

    $form['app_settings']['pager_limit'] = [
      '#title' => $this->t('Pager limit'),
      '#description' => $this->t('Items limit for blocks with pager.'),
      '#type' => 'number',
      '#default_value' => $config->get('pager_limit') ?? self::PAGER_LIMIT_DEFAULT,
      '#required' => TRUE,
    ];

    $form['app_settings']['components'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Components settings'),
    ];

    $components = [
      'gc_video' => $this->t('Virtual Y video'),
      'live_stream' => $this->t('Live streams'),
      'virtual_meeting' => $this->t('Virtual meetings'),
      'vy_blog_post' => $this->t('Blog posts'),
    ];

    foreach ($components as $id => $title) {
      $form['app_settings']['components'][$id] = [
        '#type' => 'details',
        '#open' => FALSE,
        '#title' => $title,
      ];

      $form['app_settings']['components'][$id]['status'] = [
        '#title' => $this->t('Show on the VY home page'),
        '#description' => $this->t('Enable/Disable "@name" component.', [
          '@name' => $title,
        ]),
        '#type' => 'checkbox',
        '#default_value' => $config->get('components.' . $id . '.status') ?? TRUE,
      ];

      $form['app_settings']['components'][$id]['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Block title'),
        '#required' => TRUE,
        '#default_value' => $config->get('components.' . $id . '.title'),
      ];

      $form['app_settings']['components'][$id]['up_next_title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Up next block title'),
        '#required' => TRUE,
        '#default_value' => $config->get('components.' . $id . '.up_next_title'),
      ];
    }

    $form['app_settings']['virtual_y_url'] = [
      '#type' => 'textfield',
      '#title' => 'Virtual Y Landing Page url',
      '#default_value' => $config->get('virtual_y_url'),
      '#required' => TRUE,
    ];

    $form['app_settings']['virtual_y_login_url'] = [
      '#type' => 'textfield',
      '#title' => 'Virtual Y Login Landing Page url',
      '#default_value' => $config->get('virtual_y_login_url'),
      '#required' => TRUE,
    ];

    $form['app_settings']['virtual_y_logout_url'] = [
      '#type' => 'textfield',
      '#title' => 'Virtual Y Logout url',
      '#default_value' => $config->get('virtual_y_logout_url'),
      '#description' => $this->t('Optionally provide URL for redirecting a user after logout. Redirect to the front page is default action.'),
      '#placeholder' => '/path/to/page',
    ];

    $form['app_settings']['top_menu'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Top menu settings'),

      'links_color_light' => [
        '#type' => 'textfield',
        '#title' => 'Top menu links color (light)',
        '#default_value' => $config->get('top_menu.links_color_light'),
        '#description' => $this->t('Provide color for top menu links (light)'),
        '#required' => TRUE,
      ],

      'background_color_light' => [
        '#type' => 'textfield',
        '#title' => 'Top menu background color (light)',
        '#default_value' => $config->get('top_menu.background_color_light'),
        '#description' => $this->t('Provide color for top menu background (light)'),
        '#required' => TRUE,
      ],

      'links_color_dark' => [
        '#type' => 'textfield',
        '#title' => 'Top menu links color (dark)',
        '#default_value' => $config->get('top_menu.links_color_dark'),
        '#description' => $this->t('Provide color for top menu links (dark)'),
        '#required' => TRUE,
      ],

      'background_color_dark' => [
        '#type' => 'textfield',
        '#title' => 'Top menu background color (dark)',
        '#default_value' => $config->get('top_menu.background_color_dark'),
        '#description' => $this->t('Provide color for top menu background (dark)'),
        '#required' => TRUE,
      ],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $settings = $this->config('openy_gated_content.settings');
    $permissions = $settings->get('permissions_entities');
    $settings->setData($form_state->getValue('app_settings'));
    // Hard save for setting that is not present at form.
    $settings->set('permissions_entities', $permissions);
    $settings->save();
    parent::submitForm($form, $form_state);
  }

}
