<?php

namespace Drupal\openy_gc_shared_content\Plugin\Action;

use Drupal\Core\Field\FieldUpdateActionBase;

/**
 * @Action(
 *   id = "gc_eventinstance_share_action",
 *   label = @Translation("Share to Virtual Y"),
 *   type = "eventinstance"
 * )
 */
class EventinstanceShareToVirtualY extends FieldUpdateActionBase {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsToUpdate() {
    return ['field_gc_share' => 1];
  }

}
