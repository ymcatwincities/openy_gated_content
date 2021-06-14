<?php

namespace Drupal\openy_gc_personal_training\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Personal training edit forms.
 *
 * @ingroup openy_gc_personal_training
 */
class PersonalTrainingForm extends ContentEntityForm {

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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label 1on1 Meeting.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label 1on1 Meeting.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.personal_training.collection');
  }

}
