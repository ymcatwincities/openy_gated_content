<?php

namespace Drupal\openy_gc_log;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Log entity entity.
 *
 * @see \Drupal\openy_gc_log\Entity\LogEntity.
 */
class LogEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\openy_gc_log\Entity\LogEntityInterface $entity */

    switch ($operation) {

      case 'view':

        return AccessResult::allowedIfHasPermission($account, 'view log entity entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit log entity entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete log entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add log entity entities');
  }

}
