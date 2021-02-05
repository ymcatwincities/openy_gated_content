<?php

namespace Drupal\openy_gc_log\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\openy_gc_log\Field\PayloadFieldItemList;

/**
 * Defines the Log entity entity.
 *
 * @ingroup openy_gc_log
 *
 * @ContentEntityType(
 *   id = "log_entity",
 *   label = @Translation("Virtual Y Log entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\openy_gc_log\LogEntityListBuilder",
 *     "views_data" = "Drupal\openy_gc_log\Entity\LogEntityViewsData",
 *
 *     "form" = {
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\openy_gc_log\LogEntityHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\openy_gc_log\LogEntityAccessControlHandler",
 *   },
 *   base_table = "log_entity",
 *   translatable = FALSE,
 *   admin_permission = "administer log entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "email" = "email",
 *     "event_type" = "event_type",
 *   },
 *   links = {
 *     "delete-form" = "/admin/structure/log_entity/{log_entity}/delete",
 *     "collection" = "/admin/structure/log_entity",
 *   },
 *   field_ui_base_route = "log_entity.settings"
 * )
 */
class LogEntity extends ContentEntityBase implements LogEntityInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    self::defineEmailField($fields);
    self::defineUidField($fields);
    self::defineEventTypeField($fields);
    self::defineEntityTypeField($fields);
    self::defineBundleField($fields);
    self::defineEntityIdField($fields);
    self::definePayloadField($fields);
    self::defineCreatedField($fields);
    self::defineChangedField($fields);
    self::defineMetadataField($fields);
    return $fields;
  }

  /**
   * Define email field.
   *
   * @param array $fields
   *   Fields.
   */
  public static function defineEmailField(array &$fields) {
    $fields['email'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Email'))
      ->setDescription(t('The email of the Log entity.'))
      ->setSettings([
        'max_length' => 256,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
  }

  /**
   * Define uid field.
   *
   * @param array $fields
   *   Fields.
   */
  public static function defineUidField(array &$fields) {
    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The User ID of the Log entity.'))
      ->setDefaultValue('')
      ->setSetting('target_type', 'user')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
  }

  /**
   * Define eventType field.
   *
   * @param array $fields
   *   Fields.
   */
  public static function defineEventTypeField(array &$fields) {
    $fields['event_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Event Type'))
      ->setDescription(t('The event type of the Log entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
  }

  /**
   * Define entityType field.
   *
   * @param array $fields
   *   Fields.
   */
  public static function defineEntityTypeField(array &$fields) {
    $fields['entity_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity Type'))
      ->setDescription(t('The entity type of the Log entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);
  }

  /**
   * Define bundle field.
   *
   * @param array $fields
   *   Fields.
   */
  public static function defineBundleField(array &$fields) {
    $fields['entity_bundle'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity Bundle'))
      ->setDescription(t('The bundle of the Log entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);
  }

  /**
   * Define entityId field.
   *
   * @param array $fields
   *   Fields.
   */
  public static function defineEntityIdField(array &$fields) {
    $fields['entity_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity Id'))
      ->setDescription(t('The entity id of the Log entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);
  }

  /**
   * Define payload field.
   *
   * @param array $fields
   *   Fields.
   */
  public static function definePayloadField(array &$fields) {
    $fields['payload'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Event Payload'))
      ->setComputed(TRUE)
      ->setClass(PayloadFieldItemList::class)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'weight' => 30,
      ]);
  }

  /**
   * Define created field.
   *
   * @param array $fields
   *   Fields.
   */
  public static function defineCreatedField(array &$fields) {
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));
  }

  /**
   * Define changed field.
   *
   * @param array $fields
   *   Fields.
   */
  public static function defineChangedField(array &$fields) {
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));
  }

  /**
   * Define event_metadata field.
   *
   * @param array $fields
   *   Fields.
   */
  public static function defineMetadataField(array &$fields) {
    $fields['event_metadata'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Event Metadata'))
      ->setDescription(t('The event metadata, related to the Log entity.'))
      ->setSetting('max_length', 4096)
      ->setDefaultValue('');
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
   * Check is published.
   *
   * @return bool
   *   Boolean
   */
  public function isPublished() {
    return TRUE;
  }

}
