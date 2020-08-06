<?php

namespace Drupal\openy_gc_shared_content\Plugin\Action;

use Drupal\Core\Field\FieldUpdateActionBase;

/**
 * Promotes a node.
 *
 * @Action(
 *   id = "gc_share_action",
 *   label = @Translation("Share selected content to Virtual Y"),
 *   type = "node"
 * )
 */
class ShareToVirtualY extends FieldUpdateActionBase {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsToUpdate() {
    return ['field_gc_share' => 1];
  }

}
