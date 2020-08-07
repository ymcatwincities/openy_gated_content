<?php

namespace Drupal\openy_gc_shared_content\Plugin\Action;

use Drupal\Core\Field\FieldUpdateActionBase;

/**
 * Base class for Share to Virtual Y mass actions
 */
class ShareToVirtualYBase extends FieldUpdateActionBase {

  /**
   * {@inheritdoc}
   */
  protected function getFieldsToUpdate() {
    return ['field_gc_share' => 1];
  }

}
