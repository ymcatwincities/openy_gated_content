<?php

/**
 * @file
 * Contains hook_post_update_NAME() implementations.
 */

/**
 * Delete old Daxko SSO users from system before update.
 */
function openy_gc_auth_daxko_sso_delete_old_users(&$sandbox) {

  if (!isset($sandbox['progress'])) {
    $sandbox['max'] = \Drupal::entityQuery('user')
      ->condition('roles', 'virtual_y')
      ->count()
      ->execute();
    $sandbox['ids'] = \Drupal::entityQuery('user')
      ->condition('roles', 'virtual_y')
      ->execute();
  }

  $ids = array_slice($sandbox['ids'], 0, 5);

  // Doublecheck that ids are int, not string.
  $ids = array_map(
    function ($value) {
      return (int) $value;
    },
    $ids
  );

  $users = \Drupal::entityTypeManager()
    ->getStorage('user')
    ->loadMultiple($ids);
  $not_existed = array_diff($ids, array_keys($users));

  if (!empty($not_existed)) {
    $sandbox['ids'] = array_diff($sandbox['ids'], $not_existed);
  }

  /** @var \Drupal\user\Entity\User $user */
  foreach ($users as $user) {
    $roles = $user->getRoles();
    // Delete only users that have only virtual_y role.
    if (count($roles) > 1) {
      $user->delete();
    }

    $sandbox['ids'] = array_diff($sandbox['ids'], [$user->id()]);

  }

  $sandbox['#finished'] = (count($sandbox['ids']) === 0) ? TRUE : count($sandbox['ids']) / $sandbox['max'];

  if ($sandbox['#finished']) {
    return t('Fields data were migrated for @count entities', ['@count' => $sandbox['max']]);
  }

}
