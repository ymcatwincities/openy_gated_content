<?php

namespace Drupal\openy_gc_personal_training;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface;

/**
 * Access controller for the Personal training entity.
 *
 * @see \Drupal\openy_gc_personal_training\Entity\PersonalTraining.
 */
class PersonalTrainingAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if (!$entity instanceof PersonalTrainingInterface) {
      return AccessResult::neutral()->addCacheableDependency($entity);
    }
    $active_provider = \Drupal::config('openy_gc_personal_training.settings')->get('active_provider');
    /** @var PersonalTrainingProviderInterface $plugin_instance */
    $plugin_instance = \Drupal::service('plugin.manager.personal_training_provider')->createInstance($active_provider);

    $permissions_map = [
      'update' => 'edit personal training entities',
      'delete' => 'delete personal training entities',
    ];
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIf($plugin_instance->checkPersonalTrainingAccess($account, $entity))
          ->cachePerUser()
          ->addCacheableDependency($entity);

      case 'update':
      case 'delete':
        return AccessResult::allowedIf($account->hasPermission($permissions_map[$operation]) && $plugin_instance->checkPersonalTrainingModifyAccess($account, $entity))
          ->cachePerUser()
          ->addCacheableDependency($entity);
    }

    // Unknown operation.
    return AccessResult::neutral()->cachePerUser();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add personal training entities')->cachePerPermissions();
  }

}
