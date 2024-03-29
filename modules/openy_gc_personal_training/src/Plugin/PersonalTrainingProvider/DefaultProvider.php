<?php

namespace Drupal\openy_gc_personal_training\Plugin\PersonalTrainingProvider;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\openy_gated_content\VirtualYAccessTrait;
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

  use VirtualYAccessTrait;

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
      '#title' => $this->t('1on1 Meeting role'),
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
  public function checkPersonalTrainingAccess(AccountInterface $user, PersonalTrainingInterface $personal_training):bool {
    $roles = $user->getRoles(TRUE);
    $config = $this->getConfiguration();
    $allowed_roles = [
      'administrator',
      self::$virtualYAccessEditorRole,
    ];
    if (!empty(array_intersect($allowed_roles, $roles))) {
      // Give access if user have at least one of allowed roles.
      return TRUE;
    }

    if (in_array($config['personal_trainer_role'], $roles) &&
      $personal_training->get('instructor_id')->target_id &&
      $user->id() === $personal_training->get('instructor_id')->target_id) {
      return TRUE;
    }

    // Check access for customer.
    return $personal_training->get('customer_id')->target_id &&
      $user->id() === $personal_training->get('customer_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserPersonalTrainings(AccountInterface $user, string $date_start, string $date_end): array {
    $storage = $this->entityTypeManager->getStorage('personal_training');
    $ids = $storage->getQuery()
      ->condition('customer_id', $this->currentUser->id())
      // @todo test date conditions.
      ->condition('date.value', $date_start, '>=')
      ->condition('date.end_value', $date_end, '<=')
      ->range(0, 50)
      ->execute();

    return $storage->loadMultiple($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function checkPersonalTrainingModifyAccess(AccountInterface $user, PersonalTrainingInterface $personal_training): bool {
    $roles = $user->getRoles(TRUE);
    $config = $this->getConfiguration();
    $allowed_roles = [
      'administrator',
      self::$virtualYAccessEditorRole,
    ];
    if (!empty(array_intersect($allowed_roles, $roles))) {
      // Give access if user have at least one of allowed roles.
      return TRUE;
    }

    return in_array($config['personal_trainer_role'], $roles) &&
      $personal_training->get('instructor_id')->target_id &&
      $user->id() === $personal_training->get('instructor_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function alterQuery($query, AccountInterface $account) {
    $roles = $account->getRoles(TRUE);
    $allowed_roles = [
      'administrator',
      self::$virtualYAccessEditorRole,
    ];
    if (!empty(array_intersect($allowed_roles, $roles))) {
      return;
    }
    $data_table = $this->entityTypeManager->getDefinition('personal_training')->getBaseTable();
    $or_group = $query->orConditionGroup();
    $or_group->condition("$data_table.uid", $account->id());
    $or_group->condition("$data_table.instructor_id", $account->id());
    $or_group->condition("$data_table.customer_id", $account->id());
    $query->condition($or_group);
  }

}
