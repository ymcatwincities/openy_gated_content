<?php

/**
 * @file
 * Contains hook_post_update_NAME() implementations.
 */

/**
 * Delete deprecated gc_auth_custom_user instances.
 */
function openy_gc_auth_custom_post_update_delete_custom_users(&$sandbox) {
  \Drupal::database()->truncate('gc_auth_custom_user')->execute();
}
