<?php

namespace Drupal\openy_gated_content\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\openy_gated_content\VyFavoriteItemInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the VY Favorite Item entity class.
 *
 * @ContentEntityType(
 *   id = "vy_favorite_item",
 *   label = @Translation("VY Favorite Item"),
 *   base_table = "vy_favorite_item",
 *   handlers = {
 *     "storage_schema" = "Drupal\openy_gated_content\VyFavoriteItemStorageSchema"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "ref_entity_type" = "ref_entity_type",
 *     "ref_entity_bundle" = "ref_entity_bundle",
 *     "ref_entity_id" = "ref_entity_id",
 *     "owner" = "uid",
 *     "created" = "created",
 *   },
 *   admin_permission = "administer gated content favorite items"
 * )
 */
class VyFavoriteItem extends ContentEntityBase implements VyFavoriteItemInterface {

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function getRefEntityBundle() {
    return $this->get('ref_entity_bundle')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getRefEntityType() {
    return $this->get('ref_entity_type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getRefEntityId() {
    return $this->get('ref_entity_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['ref_entity_type'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Entity type'))
      ->setDescription(new TranslatableMarkup('The entity type of the referenced entity.'))
      ->setRequired(TRUE);

    $fields['ref_entity_bundle'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Entity Bundle'))
      ->setDescription(new TranslatableMarkup('The bundle of the referenced entity.'))
      ->setRequired(TRUE);

    $fields['ref_entity_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Entity ID'))
      ->setDescription(new TranslatableMarkup('The ID of the referenced entity.'))
      ->setRequired(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the vy_favorite_item was created.'));

    return $fields;
  }

}
