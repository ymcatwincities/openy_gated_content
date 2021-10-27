<?php

/**
 * @file
 * Contains hook_post_update_NAME() implementations.
 */

/**
 * Find personal_training entities and set the bundle value to a default one.
 */
function openy_gc_personal_training_post_update_personal_trainings(&$sandbox) {
  $entity_type = 'personal_training';
  $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
  if (!isset($sandbox['progress'])) {
    $query = $storage->getQuery($entity_type)->notExists('type');
    $sandbox['ids'] = $query->execute();
    $sandbox['max'] = $query->count()->execute();
  }

  $ids = array_slice($sandbox['ids'], 0, 20);
  $personal_trainings = $storage->loadMultiple($ids);
  $not_existed = array_diff($ids, array_keys($personal_trainings));
  if (!empty($not_existed)) {
    $sandbox['ids'] = array_diff($sandbox['ids'], $not_existed);
  }

  foreach ($personal_trainings as $personal_training) {
    $personal_training->set('type', 'personal_training');
    $personal_training->save();
    $sandbox['ids'] = array_diff($sandbox['ids'], [$personal_training->id()]);
  }

  $sandbox['#finished'] = (count($sandbox['ids']) === 0) ? TRUE : count($sandbox['ids']) / $sandbox['max'];
  if ($sandbox['#finished']) {
    return t('Bundle values were updated for @count personal trainings', ['@count' => $sandbox['max']]);
  }
}

/**
 * Find personal_training entities and set the bundle value to a default one.
 */
function openy_gc_personal_training_post_update_init_owners(&$sandbox) {
  $entity_type = 'personal_training';
  $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
  if (!isset($sandbox['progress'])) {
    $query = $storage->getQuery($entity_type)->notExists('uid');
    $sandbox['ids'] = $query->execute();
    $sandbox['max'] = $query->count()->execute();
  }

  $ids = array_slice($sandbox['ids'], 0, 20);
  $personal_trainings = $storage->loadMultiple($ids);
  $not_existed = array_diff($ids, array_keys($personal_trainings));
  if (!empty($not_existed)) {
    $sandbox['ids'] = array_diff($sandbox['ids'], $not_existed);
  }

  foreach ($personal_trainings as $personal_training) {
    $personal_training->set('uid', $personal_training->instructor_id->target_id);
    $personal_training->save();
    $sandbox['ids'] = array_diff($sandbox['ids'], [$personal_training->id()]);
  }

  $sandbox['#finished'] = (count($sandbox['ids']) === 0) ? TRUE : count($sandbox['ids']) / $sandbox['max'];
  if ($sandbox['#finished']) {
    return t('Owners were initialized for @count personal trainings', ['@count' => $sandbox['max']]);
  }
}
