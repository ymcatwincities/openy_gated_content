<?php

namespace Drupal\openy_gated_content\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\Config\Config;
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

    $form['app_settings']['switch_legacy_view'] = [
      '#title' => $this->t('Switch to Legacy View'),
      '#description' => $this->t('Legacy View has the following components: Virtual Y Video, Live streams, Virtual meetings, Blog posts.'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('switch_legacy_view') ?? FALSE,
    ];

    $this->prepareComponents($form, $config);
    $this->prepareLegacyView($form, $config);

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

    $this->menuVisibilitySettings($form, $config);

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * Helper method to add menu configs.
   *
   * @param array $form
   *   Array of the form configuration to attach the form elements to.
   * @param \Drupal\Core\Config\Config $config
   *   Virtual Y config object.
   */
  protected function menuVisibilitySettings(array &$form, Config $config) {
    $form['app_settings']['menu_config'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Top Items Visibility Settings'),
      'schedule' => [
        '#title' => $this->t('Hide Schedule'),
        '#description' => $this->t("If checked 'Schedule' menu item won't be visible."),
        '#type' => 'checkbox',
        '#default_value' => $config->get('menu_config.schedule') ?? FALSE,
      ],
      'favorites' => [
        '#title' => $this->t('Hide Favorites'),
        '#description' => $this->t("If checked 'Favorites' menu item won't be visible."),
        '#type' => 'checkbox',
        '#default_value' => $config->get('menu_config.favorites') ?? FALSE,
      ],
      'categories' => [
        '#title' => $this->t('Hide Categories'),
        '#description' => $this->t("If checked 'Categories' menu item won't be visible."),
        '#type' => 'checkbox',
        '#default_value' => $config->get('menu_config.categories') ?? FALSE,
      ]
    ];
  }

  /**
   * Helper method that adds form elements for Virtual Y Components.
   *
   * @param array $form
   *   Array of the form configuration to attach the form elements to.
   * @param \Drupal\Core\Config\Config $config
   *   Virtual Y config object.
   */
  protected function prepareComponents(array &$form, Config $config) {
    // We are wrapping components into container, as it is not currently
    // possible to hide tabledrag element for table with states.
    $form['app_settings']['components_container'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Components settings'),
      '#states' => [
        'visible' => [
          ':input[id="edit-app-settings-switch-legacy-view"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['app_settings']['components_container']['components'] = [
      '#type' => 'table',
      '#title' => $this->t('Components settings'),
      '#header' => [
        $this->t('Components settings'),
        $this->t('Weight'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'weight',
        ],
      ],
    ];

    $components = [
      'categories' => $this->t('Categories'),
      'instructors' => $this->t('Instructors'),
      'duration' => $this->t('Duration'),
      'latest_content' => $this->t('Latest Content'),
    ];

    foreach ($components as $id => $title) {
      $form['app_settings']['components_container']['components'][$id] = [
        '#attributes' => [
          'class' => ['draggable'],
        ],
        '#weight' => $config->get('components.' . $id . '.weight'),
        'component' => [
          '#type' => 'details',
          '#open' => FALSE,
          '#title' => $title,
        ],
        'weight' => [
          '#type' => 'weight',
          '#default_value' => $config->get('components.' . $id . '.weight'),
          '#attributes' => [
            'class' => ['weight'],
          ],
        ],
      ];

      $form['app_settings']['components_container']['components'][$id]['component']['status'] = [
        '#title' => $this->t('Show on the VY home page'),
        '#description' => $this->t('Enable/Disable "@name" component.', [
          '@name' => $title,
        ]),
        '#type' => 'checkbox',
        '#default_value' => $config->get('components.' . $id . '.status') ?? TRUE,
      ];

      $form['app_settings']['components_container']['components'][$id]['component']['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Block title'),
        '#required' => TRUE,
        '#default_value' => $config->get('components.' . $id . '.title') ?? '',
      ];

      $form['app_settings']['components_container']['components'][$id]['component']['empty_block_text'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Text for empty block'),
        '#default_value' => $config->get('components.' . $id . '.empty_block_text') ?? '',
      ];
    }

    uasort($form['app_settings']['components_container']['components'],
      [SortArray::class, 'sortByWeightProperty']);
  }

  /**
   * Helper method that adds form elements for Legacy View components.
   *
   * @param array $form
   *   Array of the form configuration to attach the form elements to.
   * @param \Drupal\Core\Config\Config $config
   *   Virtual Y config object.
   */
  protected function prepareLegacyView(array &$form, Config $config) {
    // We are wrapping components into container, as it is not currently
    // possible to hide tabledrag element for table with states.
    $form['app_settings']['legacy_view_container'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Legacy View'),
      '#states' => [
        'visible' => [
          ':input[id="edit-app-settings-switch-legacy-view"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['app_settings']['legacy_view_container']['legacy_view'] = [
      '#type' => 'table',
      '#title' => $this->t('Legacy View'),
      '#header' => [
        $this->t('Components settings'),
        $this->t('Weight'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'weight',
        ],
      ],
    ];

    $legacy_components = [
      'gc_video' => $this->t('Virtual Y video'),
      'live_stream' => $this->t('Live streams'),
      'virtual_meeting' => $this->t('Virtual meetings'),
      'vy_blog_post' => $this->t('Blog posts'),
    ];
    $bundles_entity_types = [
      'gc_video' => 'node',
      'live_stream' => 'eventinstance',
      'virtual_meeting' => 'eventinstance',
      'vy_blog_post' => 'node',
    ];
    $date_options = [
      'node' => [
        'date_desc' => 'By Date (New-Old)',
        'date_asc' => 'By Date (Old-New)',
      ],
      'eventinstance' => [
        'date_desc' => 'By Event Date (desc)',
        'date_asc' => 'By Event Date (asc)',
      ],
    ];
    $title_options = [
      'title_asc' => 'By Title (A-Z)',
      'title_desc' => 'By Title (Z-A)',
    ];
    $video_components = [
      'gc_video',
      'live_stream',
    ];

    foreach ($legacy_components as $legacy_component_id => $legacy_component_title) {
      $form['app_settings']['legacy_view_container']['legacy_view'][$legacy_component_id] = [
        '#attributes' => [
          'class' => ['draggable'],
        ],
        '#weight' => $config->get('components.' . $legacy_component_id . '.weight'),
        'component' => [
          '#type' => 'details',
          '#open' => FALSE,
          '#title' => $legacy_component_title,
        ],
        'weight' => [
          '#type' => 'weight',
          '#default_value' => $config->get('components.' . $legacy_component_id . '.weight'),
          '#attributes' => [
            'class' => ['weight'],
          ],
        ],
      ];

      $form['app_settings']['legacy_view_container']['legacy_view'][$legacy_component_id]['component']['status'] = [
        '#title' => $this->t('Show on the VY home page'),
        '#description' => $this->t('Enable/Disable "@name" component.', [
          '@name' => $legacy_component_title,
        ]),
        '#type' => 'checkbox',
        '#default_value' => $config->get('components.' . $legacy_component_id . '.status') ?? TRUE,
      ];

      $form['app_settings']['legacy_view_container']['legacy_view'][$legacy_component_id]['component']['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Block title'),
        '#required' => TRUE,
        '#default_value' => $config->get('components.' . $legacy_component_id . '.title') ?? '',
      ];

      $form['app_settings']['legacy_view_container']['legacy_view'][$legacy_component_id]['component']['up_next_title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Up next block title'),
        '#required' => TRUE,
        '#default_value' => $config->get('components.' . $legacy_component_id . '.up_next_title') ?? '',
      ];

      $form['app_settings']['legacy_view_container']['legacy_view'][$legacy_component_id]['component']['empty_block_text'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Text for empty block'),
        '#default_value' => $config->get('components.' . $legacy_component_id . '.empty_block_text') ?? '',
      ];

      $form['app_settings']['legacy_view_container']['legacy_view'][$legacy_component_id]['component']['default_sort'] = [
        '#type' => 'select',
        '#title' => $this->t('Default view order'),
        '#options' => array_merge($date_options[$bundles_entity_types[$legacy_component_id]], $title_options),
        '#default_value' => $config->get('components.' . $legacy_component_id . '.default_sort'),
      ];

      $form['app_settings']['legacy_view_container']['legacy_view'][$legacy_component_id]['component']['show_covers'] = [
        '#title' => $this->t('Show cover image on teaser'),
        '#description' => $this->t('Allows to enable or disable display of covers on the teasers.'),
        '#type' => 'checkbox',
        '#default_value' => $config->get('components.' . $legacy_component_id . '.show_covers') ?? TRUE,
      ];

      if (in_array($legacy_component_id, $video_components)) {
        $form['app_settings']['legacy_view_container']['legacy_view'][$legacy_component_id]['component']['autoplay_videos'] = [
          '#title' => $this->t('Start videos playback automaitcally'),
          '#description' => $this->t('Videos will be autoplayed on the page load'),
          '#type' => 'checkbox',
          '#default_value' => $config->get('components.' . $legacy_component_id . '.autoplay_videos') ?? TRUE,
        ];
      }
    }

    uasort($form['app_settings']['legacy_view_container']['legacy_view'],
      [SortArray::class, 'sortByWeightProperty']);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $settings = $this->config('openy_gated_content.settings');
    $permissions = $settings->get('permissions_entities');
    $value = $form_state->getValue('app_settings');

    // Extract components values from the tree.
    $components = $value['components_container']['components'];
    $components += $value['legacy_view_container']['legacy_view'];
    $value['components'] = $components;
    unset($value['components_container']);
    unset($value['legacy_view_container']);
    array_walk($value['components'], function (&$item) {
      $item = array_merge($item['component'], ['weight' => $item['weight']]);
    });

    $settings->setData($value);
    // Hard save for setting that is not present at form.
    $settings->set('permissions_entities', $permissions);
    $settings->save();
    parent::submitForm($form, $form_state);
  }

}
