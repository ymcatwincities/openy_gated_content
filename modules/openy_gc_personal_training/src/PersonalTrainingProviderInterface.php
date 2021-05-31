<?php

namespace Drupal\openy_gc_personal_training;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Session\AccountProxyInterface;
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
  public function getConfiguration():array;

  /**
   * Check personal training access.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   User account.
   * @param \Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface $personal_training
   *   Personal Training entity.
   *
   * @return bool
   *   TRUE if user has access.
   */
  public function checkPersonalTrainingAccess(AccountProxyInterface $user, PersonalTrainingInterface $personal_training):bool;

  /**
   * Get user personal trainings.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $user
   *   User account.
   * @param string $date_start
   *   Training start time.
   * @param string $date_end
   *   Training end time.
   *
   * @return array
   *   List with personal trainings for account.
   */
  public function getUserPersonalTrainings(AccountProxyInterface $user, string $date_start, string $date_end): array;

}
