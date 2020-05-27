<?php

namespace Drupal\openy_gc_auth_custom\Plugin\migrate\source;

use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;

/**
 * Source for CSV limited by number of rows.
 *
 * @MigrateSource(
 *   id = "csv_limit"
 * )
 */
class CSVLimit extends CSV {

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    parent::initializeIterator();

    if (!isset($this->configuration['offset']) || empty($this->configuration['count'])) {
      return $this->file;
    }

    $offset = $this->configuration['offset'];
    if (!empty($this->configuration['header_row_count'])) {
      $offset += $this->configuration['header_row_count'];
    }

    return new \LimitIterator($this->file, $offset, $this->configuration['count']);
  }

}
