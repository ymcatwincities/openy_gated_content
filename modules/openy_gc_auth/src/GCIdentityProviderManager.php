<?php

namespace Drupal\openy_gc_auth;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Factory\ContainerFactory;

/**
 * Defines the base plugin for GCIdentityProvider classes.
 *
 * @see \Drupal\openy_gc_auth\GCIdentityProviderInterface
 * @see \Drupal\openy_gc_auth\Annotation\GCIdentityProvider
 * @see plugin_api
 */
class GCIdentityProviderManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/GCIdentityProvider',
      $namespaces,
      $module_handler,
      'Drupal\openy_gc_auth\GCIdentityProviderInterface',
      'Drupal\openy_gc_auth\Annotation\GCIdentityProvider'
    );

    $this->alterInfo('gc_identity_provider_info');
    $this->setCacheBackend($cache_backend, 'gc_identity_provider');
    $this->factory = new ContainerFactory($this->getDiscovery());
  }

}
