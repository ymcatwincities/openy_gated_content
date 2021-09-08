<?php

namespace Drupal\openy_gated_content\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\Config\Config;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
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
      '#description' => $this->t('Legacy View has the following components on the Home page: Virtual Y Video, Live streams, Virtual meetings, Blog posts.'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('switch_legacy_view') ?? FALSE,
    ];

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

    $components = [
      'categories' => [
        'title' => $this->t('Categories'),
      ],
      'instructors' => [
        'title' => $this->t('Instructors'),
      ],
      'duration' => [
        'title' => $this->t('Duration'),
      ],
      'latest_content' => [
        'title' => $this->t('Latest Content'),
      ],
    ];
    $form['app_settings']['components_container']['components'] = $this->prepareComponentsFormElements(
      $components,
      $this->t('Components settings'),
      $config
    );

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
    $form['app_settings']['legacy_view_container']['legacy_view'] = $this->prepareComponentsFormElements(
      $this->prepareLegacyComponents(),
      $this->t('Legacy View'),
      $config
    );

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
   * Helper method to prepare array of fields configurations for Legacy View.
   *
   * @return array[]
   *   An array of fields configurations to be passed to
   *   prepareComponentsFormElements method.
   *
   * @see \Drupal\openy_gated_content\Form\GCSettingsForm::prepareComponentsFormElements()
   */
  protected function prepareLegacyComponents() {
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

    $legacy_components = [
      'gc_video' => [
        'title' => $this->t('Virtual Y video'),
      ],
      'live_stream' => [
        'title' => $this->t('Live streams'),
      ],
      'virtual_meeting' => [
        'title' => $this->t('Virtual meetings'),
      ],
      'vy_blog_post' => [
        'title' => $this->t('Blog posts'),
      ],
    ];

    foreach ($legacy_components as $legacy_component_id => &$legacy_component_settings) {
      $legacy_component_settings['fields'] = [
        'up_next_title' => [
          'type' => 'textfield',
          'title' => $this->t('Up next block title'),
          'required' => TRUE,
        ],
        'empty_block_text' => [
          'type' => 'textfield',
          'title' => $this->t('Text for empty block'),
        ],
        'default_sort' => [
          'type' => 'select',
          'title' => $this->t('Default view order'),
          'options' => array_merge($date_options[$bundles_entity_types[$legacy_component_id]], $title_options),
        ],
        'show_covers' => [
          'type' => 'checkbox',
          'title' => $this->t('Show cover image on teaser'),
          'description' => $this->t('Allows to enable or disable display of covers on the teasers.'),
        ],
      ];

      if (in_array($legacy_component_id, $video_components)) {
        $legacy_component_settings['fields']['autoplay_videos'] = [
          'type' => 'checkbox',
          'title' => $this->t('Start videos playback automatically'),
          'description' => $this->t('Videos will be autoplayed on the page load'),
        ];
      }
    }

    return $legacy_components;
  }

  /**
   * Helper method to prepare form elements from components array.
   *
   * @param array $components
   *   Array of settings for components form elements. Each settings array is
   *   keyed by component id and has the following keys:
   *   - title: TranslatableMarkup object, representing a display title of the
   *     component;
   *   - fields: (Optional) Array of fields arrays, used by the component. Each
   *     field array is keyed by field id and contains form element keys with
   *     appropriate values.
   *   Example components array looks like the following below.
   * @code
   *   $components = [
   *     'gc_auth' => [
   *       'title' => $this->t('Virtual Y video'),
   *       'fields' => [
   *         'up_next_title' => [
   *           'type' => 'textfield',
   *           'title' => $this->t('Up next block title'),
   *           'required' => TRUE,
   *         ],
   *         'empty_block_text' => [
   *           'type' => 'textfield',
   *           'title' => $this->t('Text for empty block'),
   *         ],
   *       ],
   *     ],
   *     'virtual_meeting' => [
   *       'title' => $this->t('Virtual meetings'),
   *       'fields' => [
   *         'default_sort' => [
   *           'type' => 'select',
   *           'title' => $this->t('Default view order'),
   *           'options' => $options,
   *         ],
   *         'show_covers' => [
   *           'type' => 'checkbox',
   *           'title' => $this->t('Text for empty block'),
   *           'description' => $this->t('Allows to enable or disable display of covers on the teasers.'),
   *         ],
   *       ],
   *     ],
   *   ];
   * @endcode
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $title
   *   Title for the Components section.
   * @param \Drupal\Core\Config\Config $config
   *   Vitual Y settings configuration object.
   *
   * @return array
   *   Prepared array of components form elements.
   */
  protected function prepareComponentsFormElements(array $components, TranslatableMarkup $title, Config $config) {
    $form = [
      '#type' => 'table',
      '#title' => $title,
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

    foreach ($components as $id => $settings) {
      $form[$id] = [
        '#attributes' => [
          'class' => ['draggable'],
        ],
        '#weight' => $config->get('components.' . $id . '.weight'),
        'component' => [
          '#type' => 'details',
          '#open' => FALSE,
          '#title' => $settings['title'],
        ],
        'weight' => [
          '#type' => 'weight',
          '#default_value' => $config->get('components.' . $id . '.weight'),
          '#attributes' => [
            'class' => ['weight'],
          ],
        ],
      ];

      $form[$id]['component']['status'] = [
        '#title' => $this->t('Show on the VY home page'),
        '#description' => $this->t('Enable/Disable "@name" component.', [
          '@name' => $settings['title'],
        ]),
        '#type' => 'checkbox',
        '#default_value' => $config->get('components.' . $id . '.status') ?? TRUE,
      ];

      $form[$id]['component']['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Block title'),
        '#required' => TRUE,
        '#default_value' => $config->get('components.' . $id . '.title'),
      ];

      foreach ($settings['fields'] as $field_id => $field_config) {
        $form[$id]['component'][$field_id] = [
          '#default_value' => $config->get('components.' . $id . '.' . $field_id) ?? '',
        ];
        foreach ($field_config as $attribute => $value) {
          $form[$id]['component'][$field_id]["#$attribute"] = $value;
        }
      }
    }

    uasort($form,
      [SortArray::class, 'sortByWeightProperty']);

    return $form;
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
