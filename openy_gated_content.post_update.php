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
    $gated_content_login_page = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->create([
        'type' => 'landing_page',
        'title' => 'VIRTUAL Y Login',
        'field_content' => [$gated_content_login_prgf],
      ]);
    $gated_content_login_page->save();
    $gated_content_login_alias = $path_alias_manager->getAliasByPath('/node/' . $gated_content_login_page->id());
  }
  else {
    $gated_content_login_prgf = reset($gated_content_login_prgf);
    $gated_content_login_page = $gated_content_login_prgf->getParentEntity();
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
  }

  $gated_content_settings = \Drupal::configFactory()->getEditable('openy_gated_content.settings');
  $gated_content_settings->set('virtual_y_url', $gated_content_page_alias);
  $gated_content_settings->set('virtual_y_login_url', $gated_content_login_alias);
  $gated_content_settings->save();
}
