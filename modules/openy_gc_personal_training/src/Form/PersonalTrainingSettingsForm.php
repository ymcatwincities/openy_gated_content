<?php

namespace Drupal\openy_gc_personal_training\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Personal Training Settings Form.
 *
 * @ingroup openy_gc_personal_training
 */
class PersonalTrainingSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'personal_training_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['openy_gc_personal_training.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['provider'] = [
      '#type' => 'select',
      '#title' => $this->t('Personal training provider'),
      // TODO: get providers list from plugin manager [PRODDEV-180].
      '#options' => [
        'default' => $this->t('Default'),
        'personify' => $this->t('Personify'),
        'activenet' => $this->t('ActiveNet'),
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
    $settings = $this->config('openy_gc_personal_training.settings');
    $settings->set('provider', $form_state->getValue('provider'));
    $settings->save();
  }

}
