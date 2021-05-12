<?php


/**
 * @file
 * Hooks provided by the Virtual Y  module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter Virtual Y app settings.
 *
 * Every module could alter initial Virtual Y Vue JS app settings before
 * sending it to the front.
 *
 * @param array $backend_info
 *   Virtual Y app settings.
 *
 */
function hook_virtual_y_app_settings_alter(array &$backend_info) {
  $backend_info['personal_training_enabled'] = TRUE;
}

/**
 * @} End of "addtogroup hooks".
 */
