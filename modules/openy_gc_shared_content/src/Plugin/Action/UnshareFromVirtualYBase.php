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

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {

    /** @var \Drupal\Core\Access\AccessResultInterface $result */
    $result = $object->access('update', $account, TRUE);
    if (!$object->hasField('field_gc_share')) {
      return $result->isForbidden();
    }
    foreach ($this->getFieldsToUpdate() as $field => $value) {
      $result->andIf($object->{$field}->access('edit', $account, TRUE));
    }
    return $return_as_object ? $result : $result->isAllowed();
  }

}
