<?php

namespace Drupal\openy_gc_auth_custom\Plugin\migrate\process;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Skips processing the current row when a source value is not set.
 *
 * The skip_row_if_email_excluded process plugin checks a value not
 * exists in excluded list. Otherwise, a MigrateSkipRowException is thrown.
 *
 * Example:
 *
 * @code
 *  process:
 *    mail:
 *      plugin: vy_skip_row_if_email_excluded
 *      source: email
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "vy_skip_row_if_email_excluded",
 *   handle_multiples = TRUE
 * )
 */
class SkipRowIfEmailExcluded extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a EmailAction object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $excluded_users = $this->configFactory
      ->get('openy_gc_auth.provider.custom')
      ->get('exclude_users');

    if (in_array($value, $excluded_users)) {
      throw new MigrateSkipRowException('User with email ' . $value . ' in exclude_users list.');
    }

    return $value;
  }

}
