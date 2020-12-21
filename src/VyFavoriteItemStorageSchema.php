<?php

namespace Drupal\openy_gated_content;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the favorite item schema handler.
 */
class VyFavoriteItemStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);

    if ($data_table = $this->storage->getDataTable()) {
      $schema[$data_table]['indexes'] += [
        'vy_favorite_item__base' => [
          'ref_entity_type',
          'ref_entity_id',
          'ref_entity_bundle',
          'created',
        ],
      ];
    }

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSharedTableFieldSchema(FieldStorageDefinitionInterface $storage_definition, $table_name, array $column_mapping) {
    $schema = parent::getSharedTableFieldSchema($storage_definition, $table_name, $column_mapping);
    $field_name = $storage_definition->getName();

    switch ($field_name) {
      case 'ref_entity_type':
      case 'ref_entity_bundle':
      case 'ref_entity_id':
        // Improves the performance of the indexes defined
        // in getEntitySchema().
        $schema['fields'][$field_name]['not null'] = TRUE;
        break;

      case 'created':
        $this->addSharedTableFieldIndex($storage_definition, $schema, TRUE);
        break;
    }

    return $schema;
  }

}
