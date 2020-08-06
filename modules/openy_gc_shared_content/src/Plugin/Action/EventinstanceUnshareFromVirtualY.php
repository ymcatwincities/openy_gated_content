<?php

namespace Drupal\openy_gc_shared_content\Plugin\Action;

use Drupal\Core\Field\FieldUpdateActionBase;

/**
 * @Action(
 *   id = "gc_eventinstance_unshare_action",
 *   label = @Translation("Unshare from Virtual Y"),
 *   type = "eventinstance"
 * )
 */
class EventinstanceUnshareFromVirtualY extends FieldUpdateActionBase {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsToUpdate() {
    return ['field_gc_share' => 0];
  }

}
