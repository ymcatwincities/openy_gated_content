<?php

namespace Drupal\openy_gc_shared_content;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Factory\ContainerFactory;

/**
 * Defines the plugin manager for SharedContentSourceType classes.
 */
class SharedContentSourceTypeManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/SharedContentSourceType',
      $namespaces,
      $module_handler,
      'Drupal\openy_gc_shared_content\SharedContentSourceTypeInterface',
      'Drupal\openy_gc_shared_content\Annotation\SharedContentSourceType'
    );

    $this->alterInfo('shared_content_source_type_info');
    $this->setCacheBackend($cache_backend, 'shared_content_source_type', []);
    $this->factory = new ContainerFactory($this->getDiscovery());
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitionsByEntityType($entity_type) {
    $definitions = $this->getDefinitions();
    foreach ($definitions as $id => $definition) {
      if ($definition['entity_type'] != $entity_type) {
        unset($definitions[$id]);
      }
    }
    return $definitions;
  }

}
