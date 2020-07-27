<?php

namespace Drupal\openy_gc_log\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Log entity entities.
 */
class LogEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $this->preparePayloadViewsData($data);

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

  /**
   * Promote "Payload" entity computed field to Views Fields.
   *
   * @param array $data
   */
  public function preparePayloadViewsData(array &$data) {
    $data['log_entity']['payload'] = [
      'title' => $this->t('Payload'),
      'field' => [
        'id' => 'field',
        'default_formatter' => 'string',
        'field_name' => 'payload',
      ],
    ];
  }

}
