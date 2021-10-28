<?php

/**
 * @file
 * Contains hook_post_update_NAME() implementations.
 */

use Drupal\openy_gc_log\Entity\LogEntityInterface;

/**
 * Update all existing Virtual Y Log entities with values for metadata field.
 */
function openy_gc_log_post_update_update_metadata(&$sandbox) {
  $storage = Drupal::entityTypeManager()->getStorage('log_entity');
  if (!isset($sandbox['max'])) {
    $query = $storage->getQuery()
      ->condition('event_type', [
        LogEntityInterface::EVENT_TYPE_ENTITY_VIEW,
        LogEntityInterface::EVENT_TYPE_VIDEO_PLAYBACK_STARTED,
        LogEntityInterface::EVENT_TYPE_VIDEO_PLAYBACK_ENDED,
      ], 'IN')
      ->notExists('event_metadata');
    $sandbox['ids'] = $query->execute();
    $sandbox['max'] = $query->count()->execute();
  }
  $ids = array_slice($sandbox['ids'], 0, 50);
  $logs = $storage->loadMultiple($ids);
  foreach ($logs as $log) {
    if (!$log instanceof LogEntityInterface) {
      continue;
    }
    $vy_logger = \Drupal::getContainer()->get('openy_gc_log.logger');
    $log->set('event_metadata', serialize($vy_logger->getMetadata($log)));
    $log->save();
  }
  $sandbox['ids'] = array_diff($sandbox['ids'], $ids);
  $sandbox['#finished'] = (count($sandbox['ids']) === 0) ?: count($sandbox['ids']) / $sandbox['max'];
  if ($sandbox['#finished']) {
    return t('Metadata was saved for @count log records', ['@count' => $sandbox['max']]);
  }
}
