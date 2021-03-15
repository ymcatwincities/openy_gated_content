<?php

namespace Drupal\openy_gc_log\Plugin\views\field;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A field that displays entity timestamp field data. Supports grouping.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("openy_gc_log_duration_field")
 */
class GCLogDurationField extends FieldPluginBase {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Constructs a GCLogDurationField object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DateFormatterInterface $date_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();

    // Add the field.
    $this->field_alias = $this->query->addField(
      NULL,
      "{$this->tableAlias}.changed - {$this->tableAlias}.created",
      "{$this->tableAlias}_duration",
      ['function' => 'sum']
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getValue(ResultRow $values, $field = NULL) {
    $value = parent::getValue($values, $field);
    $from = new \DateTime();
    $to = (clone $from)->add(\DateInterval::createFromDateString("{$value} seconds"));
    return $this->dateFormatter->formatDiff($from->getTimestamp(), $to->getTimestamp());
  }

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

}
