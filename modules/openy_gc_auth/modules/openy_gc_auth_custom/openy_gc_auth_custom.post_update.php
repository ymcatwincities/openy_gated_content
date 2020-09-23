<?php

/**
 * @file
 * Contains hook_post_update_NAME() implementations.
 */

/**
 * Delete deprecated gc_auth_custom_user instances.
 *
 * Run "drush mim gc_auth_custom_users" after this update to restore customers
 * in user entity.
 */
function openy_gc_auth_custom_post_update_delete_custom_users(&$sandbox) {
  \Drupal::database()->truncate('gc_auth_custom_user')->execute();
  $schema = \Drupal::database()->schema();
  if ($schema->tableExists('migrate_map_gc_auth_custom_users')) {
    \Drupal::database()->truncate('migrate_map_gc_auth_custom_users')->execute();
  }
  if ($schema->tableExists('migrate_message_gc_auth_custom_users')) {
    \Drupal::database()->truncate('migrate_message_gc_auth_custom_users')->execute();
  }
}
