<?php

namespace Drupal\openy_gc_personal_training\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\date_recur_modular\DateRecurModularWidgetOptions;
use Drupal\openy_gated_content\VirtualYAccessTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Personal training edit forms.
 *
 * @ingroup openy_gc_personal_training
 */
class PersonalTrainingForm extends ContentEntityForm {

  use VirtualYAccessTrait;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Show remote_link only if 'training_type' is 'link'.
    $form['remote_link']['#states'] = [
      'visible' => [
        ':input[name="training_type"]' => ['value' => 'link'],
      ],
    ];

    $widget = &$form['instructor_id']['widget'];
    $widget[0]['target_id']['#default_value'] = $this->entityTypeManager
      ->getStorage('user')
      ->load($this->account->id());

    $roles = $this->account->getRoles(TRUE);
    $allowed_roles = [
      'administrator',
      self::$virtualYAccessEditorRole,
    ];
    $widget['#disabled'] = empty(array_intersect($allowed_roles, $roles));

    $groups = [
      'info' => [
        'title' => $this->t('1on1 Meeting info'),
        'open' => TRUE,
        'subitems' => [
          'title',
          'customer_id',
          'instructor_id',
          'training_type',
          'remote_link',
          'date',
          'field_schedule',
          'field_exclusions',
          'description',
          'pt_equipment',
          'field_image'
        ],
      ],
      'metadata' => [
        'title' => $this->t('1on1 Meeting Metadata'),
        'open' => FALSE,
        'subitems' => [
          'customer_metadata',
          'instructor_metadata',
          'customer_peer_id',
        ],
      ],
    ];

    foreach ($groups as $name => $group_info) {
      $form[$name] = [
        '#type' => 'details',
        '#open' => $group_info['open'],
        '#title' => $group_info['title'],
      ];
      foreach ($group_info['subitems'] as $subitem) {
        if (!isset($form[$subitem])) {
          continue;
        }
        $form[$name][$subitem] = $form[$subitem];
        unset($form[$subitem]);
      }
    }

    if ($this->entity->bundle() !== 'training_series') {
      return $form;
    }

    $timeZone = $this->config('system.date')->get('timezone')['default'];
    $widget = &$form['info']['field_schedule']['widget'];
    $schedule_elements = array_filter(Element::children($widget), 'is_int');
    foreach ($schedule_elements as $key) {
      $element = &$widget[$key];
      $item = $this->entity->field_schedule[$key];

      $element['#element_validate'] = [[static::class, 'validateModularWidget']] + $element['#element_validate'];
      $element['#theme'] = 'vy_training_series_date_recur_modular_alpha_widget';

      $element = [
        'start_date' => [
          '#type' => 'datetime',
          '#title' => $this->t('First occurrence after'),
          '#default_value' => $item->start_date,
          '#date_time_element' => 'none',
          '#date_timezone' => $timeZone,
        ],
      ] + $element;

      $element['time_zone']['#attributes'] = ['disabled' => 'disabled'];
      $element['time_zone']['#attributes']['class'][] = 'hidden';
      $element['time_zone']['#title_display'] = 'invisible';

      unset($element['start']['#title_display']);
      $element['start']['#title'] = $this->t('Starts at');
      $element['start']['#date_date_element'] = 'none';

      $element['end']['#title_display'] = 'before';
      $element['end']['#date_date_element'] = 'none';

      $element['ends_date']['ends_date']['#date_time_element'] = 'none';

      unset($element['ends_mode']['#options']['infinite']);
      if ($element['ends_mode']['#default_value'] === DateRecurModularWidgetOptions::ENDS_MODE_INFINITE) {
        $element['ends_mode']['#default_value'] = 'count';
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    $args = [
      '%label' => $entity->label(),
      '%type' => $entity->bundle() !== 'training_series' ? '1on1 Meeting' : '1on1 Meeting series',
    ];

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label %type.', $args));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label %type.', $args));
    }
    $form_state->setRedirect('entity.personal_training.collection');
  }

  /**
   * Validates the widget.
   *
   * @param array $element
   *   The element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   */
  public static function validateModularWidget(array &$element, FormStateInterface $form_state, array &$complete_form): void {
    /** @var \Drupal\Core\Datetime\DrupalDateTime|array|null $start */
    $start = $form_state->getValue(array_merge($element['#parents'], ['start']));
    /** @var \Drupal\Core\Datetime\DrupalDateTime|array|null $end */
    $end = $form_state->getValue(array_merge($element['#parents'], ['end']));
    if ($start && $end && $end->getTimestamp() < $start->getTimestamp()) {
      $form_state->setError($element['end'], \t('End time should not be less than the start time.'));
    }
    /** @var \Drupal\Core\Datetime\DrupalDateTime|array|null $endsDate */
    $endsDate = $form_state->getValue(array_merge($element['#parents'], ['ends_date']));
    if ($endsDate instanceof DrupalDateTime && $end instanceof DrupalDateTime) {
      $endsDate = DrupalDateTime::createFromFormat(DrupalDateTime::FORMAT, $endsDate->format('Y-m-d') . ' ' . $end->format('H:i:s'));
      $form_state->setValueForElement($element['ends_date'], $endsDate);
    }

    /** @var \Drupal\Core\Datetime\DrupalDateTime|array|null $startDate */
    $startDate = $form_state->getValue(array_merge($element['#parents'], ['start_date']));

    $mode = $form_state->getValue(array_merge($element['#parents'], ['mode']));
    $endsMode = $form_state->getValue(array_merge($element['#parents'], ['ends_mode']));
    $end_date_sensitive_modes = [
      'weekly',
      'fortnightly',
      'monthly',
    ];
    if (!$endsDate instanceof DrupalDateTime && $endsMode === DateRecurModularWidgetOptions::ENDS_MODE_ON_DATE && in_array($mode, $end_date_sensitive_modes)) {
      $form_state->setError($element['ends_date'], \t('Ends before date must be provided for this mode.'));
    }

    if ($startDate instanceof DrupalDateTime && $start instanceof DrupalDateTime) {
      $start = DrupalDateTime::createFromFormat(DrupalDateTime::FORMAT, $startDate->format('Y-m-d') . ' ' . $start->format('H:i:s'));
      $form_state->setValueForElement($element['start'], $start);
    }
    if ($startDate instanceof DrupalDateTime && $end instanceof DrupalDateTime) {
      $end = DrupalDateTime::createFromFormat(DrupalDateTime::FORMAT, $startDate->format('Y-m-d') . ' ' . $end->format('H:i:s'));
      $form_state->setValueForElement($element['end'], $end);
    }
  }

}
