<?php

namespace Drupal\openy_gc_shared_content_server;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the Shared content source entity.
 *
 * @see \Drupal\openy_gc_shared_content_server\Entity\SharedContentSource.
 */
class SharedContentSourceAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\openy_gc_shared_content_server\Entity\SharedContentSource $entity */

    switch ($operation) {

      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view shared content source entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit shared content source entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete shared content source entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add shared content source entities');
  }

}
