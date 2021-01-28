<?php

namespace Drupal\openy_gc_personal_training\Plugin\PersonalTrainingProvider;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface;
use Drupal\openy_gc_personal_training\PersonalTrainingProviderPluginBase;

/**
 * Default Personal Training provider plugin.
 *
 * @PersonalTrainingProvider(
 *   id="default",
 *   label = @Translation("Default provider"),
 *   config="openy_gc_personal_training.provider.default"
 * )
 */
class DefaultProvider extends PersonalTrainingProviderPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration():array {
    return [
      'personal_trainer_role' => 'personal_trainer',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state):array {
    $config = $this->getConfiguration();
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['personal_trainer_role'] = [
      '#type' => 'select',
      '#title' => $this->t('Personal training role'),
      '#required' => TRUE,
      '#default_value' => $config['personal_trainer_role'],
      '#options' => [],
    ];

    $roles = user_roles(TRUE);
    foreach ($roles as $role_id => $role) {
      $form['personal_trainer_role']['#options'][$role_id] = $role->label();
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $this->configuration['personal_trainer_role'] = $form_state->getValue('personal_trainer_role');
      parent::submitConfigurationForm($form, $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function checkPersonalTrainingAccess(AccountProxyInterface $user, PersonalTrainingInterface $personal_training):bool {
    $roles = $user->getRoles();
    if (in_array('administrator', $roles)) {
      // TODO: do we need access for admins here?
      return TRUE;
    }

    $config = $this->getConfiguration();
    if (in_array($config['personal_trainer_role'], $roles)) {
      // Check access for personal trainer role.
      return $user->id() === $personal_training->get('instructor_id')->target_id;
    }

    // Check access for customer.
    return $user->id() === $personal_training->get('customer_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserPersonalTrainings(AccountProxyInterface $user, string $date_start, string $date_end): array {
    // TODO add implementation for default provider.
    // What we should load here, PersonalTraining entities?
    return [];
  }

}
