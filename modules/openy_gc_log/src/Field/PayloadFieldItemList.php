<?php

namespace Drupal\openy_gc_log\Field;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\openy_gc_log\Entity\LogEntityInterface;

/**
 * Class Payload Field ItemList.
 *
 * Computed field for LogEntity.
 * Composed from event_type related fields.
 *
 * @package Drupal\openy_gc_log\Field
 */
class PayloadFieldItemList extends FieldItemList {

  use ComputedItemListTrait;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The Gated Content Logger.
   *
   * @var \Drupal\openy_gc_log\Logger
   */
  protected $gcLogger;

  /**
   * Constructs a TypedData object given its definition and context.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $definition
   *   The data definition.
   * @param string $name
   *   The name of the created property.
   * @param \Drupal\Core\TypedData\TypedDataInterface $parent
   *   The parent object of the data property.
   *
   * @see \Drupal\Core\TypedData\TypedDataManager::create()
   */
  public function __construct(DataDefinitionInterface $definition, $name, TypedDataInterface $parent) {
    parent::__construct($definition, $name, $parent);
    $this->dateFormatter = \Drupal::getContainer()->get('date.formatter');
    $this->gcLogger = \Drupal::getContainer()->get('openy_gc_log.logger');
  }

  /**
   * Compute value.
   *
   * @inheritDoc
   */
  protected function computeValue() {
    /**
     * @var \Drupal\openy_gc_log\Entity\LogEntity $log
     */
    $log = $this->getEntity();

    switch ($log->get('event_type')->value) {
      case LogEntityInterface::EVENT_TYPE_ENTITY_VIEW:
      case LogEntityInterface::EVENT_TYPE_VIDEO_PLAYBACK_STARTED:
      case LogEntityInterface::EVENT_TYPE_VIDEO_PLAYBACK_ENDED:
        $metadata = $this->gcLogger->getMetadata($log);
        if (empty($metadata)) {
          $metadata = unserialize($log->get('event_metadata')->value, ['allowed_classes' => FALSE]);
        }
        $value = $metadata['entity_title'];
        if (array_key_exists('entity_instructor_name', $metadata) && $instructor_name = $metadata['entity_instructor_name']) {
          $value .= ' (Instructor name: ' . $instructor_name . ')';
        }
        break;

      case LogEntityInterface::EVENT_TYPE_USER_ACTIVITY:
        $value = $this->dateFormatter->formatDiff($log->getCreatedTime(), $log->getChangedTime());
        break;

      default:
        $value = '';
    }

    $this->list[0] = $this->createItem(0, $value);
  }

}
