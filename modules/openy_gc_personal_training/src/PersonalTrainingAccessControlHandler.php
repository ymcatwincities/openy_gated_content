<?php

namespace Drupal\openy_gc_personal_training;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

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
    switch ($operation) {
      case 'view':
        $active_provider = \Drupal::config('openy_gc_personal_training.settings')->get('active_provider');
        $plugin_instance = \Drupal::service('plugin.manager.personal_training_provider')->createInstance($active_provider);
        if ($plugin_instance->checkPersonalTrainingAccess($account, $entity)) {
          return AccessResult::allowed()->cachePerUser();
        }
        break;

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit personal training entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete personal training entities');
    }

    // Unknown operation, close access.
    return AccessResult::forbidden();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add personal training entities');
  }

}
