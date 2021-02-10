<?php

/**
 * @file
 * Contains hook_post_update_NAME() implementations.
 */

/**
 * Find gated content pages and update new config params with URL's.
 */
function openy_gated_content_post_update_create_login_page(&$sandbox) {
  $prgf_storage = \Drupal::entityTypeManager()->getStorage('paragraph');
  $path_alias_manager = \Drupal::service('path_alias.manager');
  $gated_content_prgf = $prgf_storage->loadByProperties(['type' => 'gated_content']);
  if (!$gated_content_prgf) {
    // There no Virtual Y on this site.
    return;
  }

  $gated_content_prgf = reset($gated_content_prgf);
  $gated_content_page = $gated_content_prgf->getParentEntity();
  if (!$gated_content_page) {
    return;
  }
  $gated_content_page_alias = $path_alias_manager->getAliasByPath('/node/' . $gated_content_page->id());
  $gated_content_login_prgf = $prgf_storage->loadByProperties(['type' => 'gated_content_login']);
  if (!$gated_content_login_prgf) {
    // Create Virtual Y login page and paragraph.
    $gated_content_login_prgf = $prgf_storage->create([
      'type' => 'gated_content_login',
    ]);
    $gated_content_login_page = FALSE;
  }
  else {
    $gated_content_login_prgf = reset($gated_content_login_prgf);
    $gated_content_login_page = $gated_content_login_prgf->getParentEntity();
  }

  if (!$gated_content_login_page) {
    $gated_content_login_page = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->create([
        'type' => 'landing_page',
        'title' => 'VIRTUAL Y Login',
        'field_content' => [$gated_content_login_prgf],
      ]);
    $gated_content_login_page->save();
  }
  $gated_content_login_alias = $path_alias_manager->getAliasByPath('/node/' . $gated_content_login_page->id());

  $gated_content_settings = \Drupal::configFactory()->getEditable('openy_gated_content.settings');
  $gated_content_settings->set('virtual_y_url', $gated_content_page_alias);
  $gated_content_settings->set('virtual_y_login_url', $gated_content_login_alias);
  $gated_content_settings->save();
}

/**
 * Helper function for post updates of permissions field.
 */
function _openy_gated_content_permissions(&$sandbox, string $entity_type, string $bundle, $id = 'nid') {
  if (!isset($sandbox['progress'])) {
    $sandbox['max'] = \Drupal::entityQuery($entity_type)
      ->condition('type', $bundle)
      ->notExists('field_vy_permission')
      ->count()
      ->execute();
    $sandbox['ids'] = \Drupal::entityQuery($entity_type)
      ->condition('type', $bundle)
      ->notExists('field_vy_permission')
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
  $nodes = \Drupal::entityTypeManager()
    ->getStorage($entity_type)
    ->loadMultiple($ids);
  $not_existed = array_diff($ids, array_keys($nodes));
  if (!empty($not_existed)) {
    $sandbox['ids'] = array_diff($sandbox['ids'], $not_existed);
  }

  foreach ($nodes as $node) {
    $node->field_vy_permission->value = 'virtual_y,virtual_y_premium';
    $node->save();
    $sandbox['ids'] = array_diff($sandbox['ids'], [$node->id()]);

  }
  $sandbox['#finished'] = (count($sandbox['ids']) === 0) ? TRUE : count($sandbox['ids']) / $sandbox['max'];
  if ($sandbox['#finished']) {
    return t('Fields data were migrated for @count entities', ['@count' => $sandbox['max']]);
  }
}

/**
 * Update all existed Virtual Y Videos with default permissions field.
 */
function openy_gated_content_post_update_permissions_video(&$sandbox) {
  _openy_gated_content_permissions($sandbox, 'node', 'gc_video');
}

/**
 * Update all existed Virtual Y Blog Posts with default permissions field.
 */
function openy_gated_content_post_update_permissions_blog_posts(&$sandbox) {
  _openy_gated_content_permissions($sandbox, 'node', 'vy_blog_post');
}

/**
 * Update all existed Virtual Y Online streams with default permissions field.
 */
function openy_gated_content_post_update_eventseries_livestream(&$sandbox) {
  _openy_gated_content_permissions($sandbox, 'eventseries', 'live_stream', 'id');
}

/**
 * Update all existed Virtual Y Virtual meetings with default permissions field.
 */
function openy_gated_content_post_update_eventseries_meeting(&$sandbox) {
  _openy_gated_content_permissions($sandbox, 'eventseries', 'virtual_meeting', 'id');
}

/**
 * Set gated_content paragraphs title and description if it was empty.
 */
function openy_gated_content_post_update_paragraph_headline(&$sandbox) {
  $prgf_storage = \Drupal::entityTypeManager()->getStorage('paragraph');
  $gated_content_prgf = $prgf_storage->loadByProperties(['type' => 'gated_content']);
  $gated_content_prgf = end($gated_content_prgf);
  if ($gated_content_prgf) {
    $page_id = $gated_content_prgf->parent_id->value;

    $header_prgf = $prgf_storage->loadByProperties([
      'type' => ['small_banner', 'banner'],
      'parent_id' => $page_id,
    ]
    );

    $header_prgf = end($header_prgf);

    $title = 'Virtual YMCA';
    $description = '<p>Find the newest Y classes and programs</p><p><a class="btn btn-primary" href="#/live-stream"><span class="text">Live Streams</span></a>&nbsp; <a class="btn btn-primary" href="#/categories/video"><span class="text">Videos</span></a></p>';
    if ($header_prgf) {
      if (!empty($header_prgf->field_prgf_headline->value)) {
        $title = $header_prgf->field_prgf_headline->value;
      }
      if (!empty($header_prgf->field_prgf_image->first())) {
        $image = $header_prgf->field_prgf_image->first();
      }
      if (!empty($header_prgf->field_prgf_description->value)) {
        $description = $header_prgf->field_prgf_description->value;
      }
      $header_prgf->delete();
    }

    if (empty($gated_content_prgf->field_prgf_title->value)) {
      $gated_content_prgf->field_prgf_title->value = $title;
    }
    if (empty($gated_content_prgf->field_prgf_description->value)) {
      $gated_content_prgf->field_prgf_description->value = $description;
      $gated_content_prgf->field_prgf_description->format = 'full_html';
    }
    if (empty($gated_content_prgf->field_prgf_image->first())) {
      $gated_content_prgf->field_prgf_image->set(0, $image);
    }
    $gated_content_prgf->save();
  }
}
