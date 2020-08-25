<?php

namespace Drupal\openy_gc_shared_content\Plugin\Action;

use Drupal\Core\Field\FieldUpdateActionBase;

/**
 * Base class for Share to Virtual Y mass actions.
 */
class ShareToVirtualYBase extends FieldUpdateActionBase {

  const GC_SHARE_ENABLED = 1;

  /**
   * {@inheritdoc}
   */
  protected function getFieldsToUpdate() {
    return ['field_gc_share' => self::GC_SHARE_ENABLED];
  }

}
