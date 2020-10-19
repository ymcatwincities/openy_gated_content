<?php

namespace Drupal\openy_gc_auth_custom\Plugin\migrate\source;

use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;

/**
 * Source for CSV limited by number of rows.
 *
 * @MigrateSource(
 *   id = "csv_limit"
 * )
 *
 * @deprecated in openy_gated_content:1.01.
 */
class CSVLimit extends CSV {

}
