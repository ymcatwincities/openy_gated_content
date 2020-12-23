<?php

namespace Drupal\openy_gc_auth_custom\Plugin\migrate\source;

use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;

/**
 * Source plugin for CSV.
 *
 * The only difference with parent - return MapIterator instead of generator
 * in the initializeIterator().
 * This is fix for executing migration from UI, otherwise we will get an error
 * "Cannot rewind a generator that was already run in SourcePluginBase.php".
 *
 * @MigrateSource(
 *   id = "csv_limit"
 * )
 */
class CSVLimit extends CSV {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\migrate\MigrateException
   * @throws \League\Csv\Exception
   */
  public function initializeIterator() {
    $header = $this->getReader()->getHeader();
    if ($this->configuration['fields']) {
      // If there is no header record, we need to flip description and name so
      // the name becomes the header record.
      $header = array_flip($this->fields());
    }
    // Return MapIterator instead of generator.
    return $this->getReader()->getRecords($header);
  }

}
