<?php

namespace Drupal\openy_gc_personal_training;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface;

/**
 * Defines the common interface for all PersonalTrainingProvider plugins.
 *
 * @see \Drupal\openy_gc_personal_training\PersonalTrainingProviderManager
 * @see \Drupal\openy_gc_personal_training\Annotation\PersonalTrainingProvider
 * @see plugin_api
 */
interface PersonalTrainingProviderInterface extends PluginInspectionInterface, ConfigurableInterface, PluginFormInterface {

  /**
   * Get PersonalTrainingProvider plugin id.
   */
  public function getId();

  /**
   * Get PersonalTrainingProvider plugin label.
   */
  public function getLabel();

  /**
   * Get plugin configuration name.
   */
  public function getConfigName();

  /**
   * Get plugin configuration.
   */
  public function getConfiguration(): array;

  /**
   * Check personal training access.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   User account.
   * @param \Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface $personal_training
   *   Personal Training entity.
   *
   * @return bool
   *   TRUE if user has access.
   */
  public function checkPersonalTrainingAccess(AccountInterface $user, PersonalTrainingInterface $personal_training): bool;

  /**
   * Check personal training CRUD access.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   User account.
   * @param \Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface $personal_training
   *   Personal Training entity.
   *
   * @return bool
   *   TRUE if user has access to manage entity.
   */
  public function checkPersonalTrainingModifyAccess(AccountInterface $user, PersonalTrainingInterface $personal_training): bool;

  /**
   * Alters a query when personal_training access is required.
   *
   * @param mixed $query
   *   Query that is being altered.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   A user object representing the user for whom the operation is to be
   *   performed.
   */
  public function alterQuery($query, AccountInterface $account);

  /**
   * Get user personal trainings.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   User account.
   * @param string $date_start
   *   Training start time.
   * @param string $date_end
   *   Training end time.
   *
   * @return array
   *   List with personal trainings for account.
   */
  public function getUserPersonalTrainings(AccountInterface $user, string $date_start, string $date_end): array;

}
