<?php

namespace Drupal\openy_gc_personal_training;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Factory\ContainerFactory;

/**
 * Defines the base plugin for PersonalTrainingProvider classes.
 *
 * @see \Drupal\openy_gc_personal_training\PersonalTrainingProviderInterface
 * @see \Drupal\openy_gc_personal_training\Annotation\PersonalTrainingProvider
 * @see plugin_api
 */
class PersonalTrainingProviderManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/PersonalTrainingProvider',
      $namespaces,
      $module_handler,
      'Drupal\openy_gc_personal_training\PersonalTrainingProviderInterface',
      'Drupal\openy_gc_personal_training\Annotation\PersonalTrainingProvider'
    );

    $this->alterInfo('personal_training_provider_info');
    $this->setCacheBackend($cache_backend, 'personal_training_provider');
    $this->factory = new ContainerFactory($this->getDiscovery());
  }

}
