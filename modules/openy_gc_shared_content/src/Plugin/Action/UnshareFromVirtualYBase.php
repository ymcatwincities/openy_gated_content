<?php

namespace Drupal\openy_gc_shared_content\Plugin\Action;

use Drupal\Core\Field\FieldUpdateActionBase;

/**
 * Base class for Unshare from Virtual Y mass actions.
 */
class UnshareFromVirtualYBase extends FieldUpdateActionBase {

  const GC_SHARE_DEFAULT_VALUE = 0;

  /**
   * {@inheritdoc}
   */
  protected function getFieldsToUpdate() {
    return ['field_gc_share' => self::GC_SHARE_DEFAULT_VALUE];
  }

}
