<?php

namespace Drupal\openy_gc_shared_content\Plugin\Action;

use Drupal\Core\Field\FieldUpdateActionBase;

/**
 * Promotes a node.
 *
 * @Action(
 *   id = "gc_unshare_action",
 *   label = @Translation("Unshare selected content from Virtual Y"),
 *   type = "node"
 * )
 */
class UnshareFromVirtualY extends FieldUpdateActionBase {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsToUpdate() {
    return ['field_gc_share' => 0];
  }

}
