<?php

namespace Drupal\openy_gc_log\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * A field that displays entity timestamp field data. Supports grouping.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("openy_gc_log_day_field")
 */
class GCLogDayField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();

    // Add the field.
    $params = $this->options['group_type'] !== 'group' ? ['function' => $this->options['group_type']] : [];

    $formula = $this->query->getDateFormat("FROM_UNIXTIME($this->tableAlias.$this->realField)", 'D, d/m/Y');

    $this->field_alias = $this->query->addField(NULL, $formula, "{$this->tableAlias}_{$this->realField}", $params);
    $this->query->addGroupBy($this->field_alias);
  }

}
