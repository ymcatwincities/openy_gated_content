<?php

namespace Drupal\openy_gc_shared_content_server;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * SourceRepository.
 */
class SourceRepository {

  /**
   * Mapping storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->storage = $entity_type_manager->getStorage('shared_content_source');
  }

  /**
   * Load all sources.
   *
   * @return array
   *   An array of found source objects sorted by name.
   */
  public function loadAll() {
    $source_ids = $this->storage->getQuery()
      ->sort('name', 'ASC')
      ->execute();
    if (!$source_ids) {
      return [];
    }

    return $this->storage->loadMultiple($source_ids);
  }

  /**
   * Loads one or more entities.
   *
   * @param array $source_ids
   *   An array of entity IDs, or NULL to load all entities.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of entity objects indexed by their IDs. Returns an empty array
   *   if no matching entities are found.
   */
  public function loadMultiple(array $source_ids) {
    return $this->storage->loadMultiple($source_ids);
  }

}
