<?php

namespace Drupal\openy_gc_shared_content\Plugin\Action;

use Drupal\Core\Field\FieldUpdateActionBase;

/**
 *
 */
class UnshareFromVirtualYBase extends FieldUpdateActionBase {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsToUpdate() {
    return ['field_gc_share' => 0];
  }

}
