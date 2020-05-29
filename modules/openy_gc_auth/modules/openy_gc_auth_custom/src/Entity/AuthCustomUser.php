<?php

namespace Drupal\openy_gc_auth_custom\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the AuthCustomUser entity class.
 *
 * @ContentEntityType(
 *   id = "gc_auth_custom_user",
 *   label = @Translation("Auth Custom User"),
 *   base_table = "gc_auth_custom_user",
 *   entity_keys = {
 *     "id" = "id"
 *   },
 *   translatable = FALSE,
 *   fieldable = FALSE,
 *   admin_permission = "administer site configuration",
 * )
 */
class AuthCustomUser extends ContentEntityBase implements AuthCustomUserInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    // Note: all fields can be overridden in hook_entity_base_field_info_alter.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The entity ID.'))
      ->setReadOnly(TRUE);

    $fields['member_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Member ID'))
      ->setDescription(t('Unique Member id.'));

    $fields['primary'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Primary Member'))
      ->setDescription(t('A boolean indicating whether the Member is Primary or not.'))
      ->setDefaultValue(TRUE);

    $fields['first_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('First Name'))
      ->setDescription(t('Member First Name.'));

    $fields['email'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Email'))
      ->setDescription(t('Member Email.'));

    $fields['package_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Package Name'))
      ->setDescription(t('Member Package Name.'));

    $fields['package_site'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Package Site'))
      ->setDescription(t('Member Package Site.'));

    return $fields;
  }

}
