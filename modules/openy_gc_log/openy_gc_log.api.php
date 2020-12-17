<?php

/**
 * @file
 * Hooks related to the openy_gc_log module.
 */

use Drupal\openy_gc_log\Entity\LogEntityInterface;

/**
 * Alter the data would be exported in a row.
 *
 * @param array $export_row
 *   An array of data forming a row in the resulting export file(s).
 * @param \Drupal\openy_gc_log\Entity\LogEntityInterface $log
 *   Log entity containing data about the logged event.
 */
function hook_openy_gc_log_export_row_alter(array &$export_row, LogEntityInterface $log) {
  // Add the user ID to an export.
  $export_row['user_id'] = $log->get('uid')->target_id;

  // Remove the users' emails from an export.
  unset($export_row['user']);
}
