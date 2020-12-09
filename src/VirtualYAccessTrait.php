<?php

namespace Drupal\openy_gated_content;

/**
 * Trait Virtual Y Access Trait.
 *
 * @package Drupal\openy_gated_content
 */
trait VirtualYAccessTrait {

  /**
   * Constant with virtual y editor role.
   *
   * @var string
   */
  public static $virtualYAccessEditorRole = 'virtual_ymca_editor';

  /**
   * Get list of roles that could administer virtual y content.
   *
   * @return array
   *   List of Virtual Y Editor roles.
   */
  public function getVirtualyEditorRoles() {
    return [self::$virtualYAccessEditorRole];
  }

}
