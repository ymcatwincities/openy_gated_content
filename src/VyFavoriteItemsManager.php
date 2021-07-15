<?php

namespace Drupal\openy_gated_content;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class for managing VyFavoriteItem entities.
 */
class VyFavoriteItemsManager implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * VyFavoriteItemsManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Deletes vy_favorite_item instances related to the content entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity that was just deleted or should be omitted for other reason.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function deleteRelatedFavoriteItems(ContentEntityInterface $entity): void {
    $favorite_items = $this->entityTypeManager
      ->getStorage('vy_favorite_item')
      ->loadByProperties([
        'ref_entity_type' => $entity->getEntityTypeId(),
        'ref_entity_bundle' => $entity->bundle(),
        'ref_entity_id' => $entity->id(),
      ]);
    if (empty($favorite_items)) {
      return;
    }
    foreach ($favorite_items as $favorite_item) {
      $favorite_item->delete();
    }
  }

}
