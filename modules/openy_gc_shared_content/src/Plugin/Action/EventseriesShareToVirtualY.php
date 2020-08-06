<?php

namespace Drupal\openy_gc_shared_content\Plugin\Action;

use Drupal\Core\Field\FieldUpdateActionBase;

/**
 * Mass action "Share to Virtual Y".
 *
 * @Action(
 *   id = "gc_eventseries_share_action",
 *   label = @Translation("Share to Virtual Y"),
 *   type = "eventseries"
 * )
 */
class EventseriesShareToVirtualY extends FieldUpdateActionBase {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsToUpdate() {
    return ['field_gc_share' => 1];
  }

}
