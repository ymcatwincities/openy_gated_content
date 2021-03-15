<?php

/**
 * @file
 * This file contains the hook to assign new handler to date fields.
 */

/**
 * Implements hook_views_data().
 */
function openy_gc_log_views_data() {
  return [
    'log_entity' => [
      'aggregated_date' => [
        'title' => t('Activity date'),
        'help' => t('Date, formatted without time, to be able to group by.'),
        'real field' => 'created',
        'field' => [
          'id' => 'openy_gc_log_day_field',
        ],
      ],
      'total_duration' => [
        'title' => t('Activity duration'),
        'help' => t('Renders a human-readable activity time interval.'),
        'field' => [
          'id' => 'openy_gc_log_duration_field',
        ],
      ],
    ],
  ];
}
