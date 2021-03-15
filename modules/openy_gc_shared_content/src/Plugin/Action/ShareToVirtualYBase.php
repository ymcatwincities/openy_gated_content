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
  
  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {

    if (!$object->hasField('field_gc_share')) {
       \Drupal::messenger()->addError(t('Error message.'));
       return FALSE;
    }
    /** @var \Drupal\Core\Access\AccessResultInterface $result */
    $result = $object->access('update', $account, TRUE);

    foreach ($this->getFieldsToUpdate() as $field => $value) {
      $result->andIf($object->{$field}->access('edit', $account, TRUE));
    }

    return $return_as_object ? $result : $result->isAllowed();
   }
}
