<?php

namespace Drupal\openy_gc_log\Field;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\openy_gc_log\Entity\LogEntityInterface;

class PayloadFieldItemList extends FieldItemList {

  use ComputedItemListTrait;

  protected function computeValue() {
    /* @var \Drupal\openy_gc_log\Entity\LogEntity $log */
    $log = $this->getEntity();

    $value = '';
    $eventType = $log->get('event_type')->value;
    if ($eventType === LogEntityInterface::EVENT_TYPE_ENTITY_VIEW) {
      $entityType = $log->get('entity_type')->value;
      $bundle = $log->get('bundle')->value;
      $entityId = $log->get('entity_id')->value;

      $value = "$entityType:$bundle/$entityId";
    }

    $this->list[0] = $this->createItem(0, $value);
  }

}
