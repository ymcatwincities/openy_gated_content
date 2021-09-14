<?php

/**
 * @file
 * Contains hook_post_update_NAME() implementations.
 */

/**
 * Helper function for migrate instructor fields.
 */
function _openy_gc_storage_migrate_instructors(&$sandbox, string $entity_type, string $legacyFieldName) {
  if (!isset($sandbox['max'])) {
    $sandbox['ids'] = \Drupal::entityQuery($entity_type)
      ->exists($legacyFieldName)
      ->execute();
    $sandbox['max'] = count($sandbox['ids']);
  }

  $ids = array_slice($sandbox['ids'], 0, 5);

  // Doublecheck that ids are int, not string.
  $ids = array_map(
    function ($value) {
      return (int) $value;
    },
    $ids
  );
  $entities = \Drupal::entityTypeManager()
    ->getStorage($entity_type)
    ->loadMultiple($ids);
  $notExisted = array_diff($ids, array_keys($entities));
  if (!empty($notExisted)) {
    $sandbox['ids'] = array_diff($sandbox['ids'], $notExisted);
  }
  $termStorage = \Drupal::entityTypeManager()
    ->getStorage('taxonomy_term');

  foreach ($entities as $entity) {
    $sandbox['ids'] = array_diff($sandbox['ids'], [$entity->id()]);
    $instructorNames = explode(',', $entity->{$legacyFieldName}->value);
    if (empty($instructorNames)) {
      continue;
    }
    $entity->field_gc_instructor_reference = [];
    foreach ($instructorNames as $instructorName) {
      $trimmedName = trim($instructorName);
      $instructorsIds = $termStorage->getQuery()
        ->condition('name', $trimmedName)
        ->range(0, 1)
        ->execute();
      if (!empty($instructorsIds)) {
        $term = $termStorage->loadMultiple($instructorsIds);
        $term = reset($term);
      }
      else {
        $term = $termStorage->create([
          'vid' => 'gc_instructor',
          'name' => $trimmedName,
        ]);
        $term->save();
      }
      $entity->field_gc_instructor_reference[] = ['target_id' => $term->id()];
    }
    $entity->save();
  }

  $sandbox['#finished'] = (count($sandbox['ids']) === 0) ? TRUE : count($sandbox['ids']) / $sandbox['max'];
  if ($sandbox['#finished']) {
    return t('Fields data were migrated for @count entities', ['@count' => $sandbox['max']]);
  }
}

/**
 * Update all existed Virtual Y Videos' with the instructor references.
 */
function openy_gc_storage_post_update_migrate_node_instructors(&$sandbox) {
  _openy_gc_storage_migrate_instructors($sandbox, 'node', 'field_gc_video_instructor');
}

/**
 * Update all existed Virtual Y eventseries' with the instructor references.
 */
function openy_gc_storage_post_update_migrate_eventseries_instructors(&$sandbox) {
  _openy_gc_storage_migrate_instructors($sandbox, 'eventseries', 'field_ls_host_name');
}

/**
 * Update all existed Virtual Y eventinstances' with the instructor references.
 */
function openy_gc_storage_post_update_migrate_eventinstance_instructors(&$sandbox) {
  _openy_gc_storage_migrate_instructors($sandbox, 'eventinstance', 'field_ls_host_name');
}
