<?php

namespace Drupal\openy_gc_shared_content\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines dynamic local tasks.
 */
class SourceServerLocalTasks extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The plugin manager for SharedContentSourceType classes.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $sharedSourceTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, PluginManagerInterface $manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->sharedSourceTypeManager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.shared_content_source_type')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $servers = $this->entityTypeManager
      ->getStorage('shared_content_source_server')
      ->loadMultiple();

    if (empty($servers)) {
      return parent::getDerivativeDefinitions($base_plugin_definition);
    }

    $this->derivatives = [];
    foreach ($servers as $server) {
      $weight = 0;
      foreach ($this->sharedSourceTypeManager->getDefinitions() as $plugin_id => $plugin) {
        $instance = $this->sharedSourceTypeManager->createInstance($plugin_id);
        $this->derivatives['openy_gc_shared_content.fetch.' . $server->id() . '.' . $instance->getId()] = [
          'title' => $instance->getLabel(),
          'route_name' => 'entity.shared_content_source_server.fetch_form',
          'base_route' => 'entity.shared_content_source_server.fetch_form',
          'route_parameters' => [
            'shared_content_source_server' => $server->id(),
            'type' => $instance->getId(),
          ],
          'weight' => $weight++,
        ];
      }
    }
    return $this->derivatives;
  }

}
