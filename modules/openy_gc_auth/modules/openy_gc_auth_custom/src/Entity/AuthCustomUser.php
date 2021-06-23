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
 *   handlers = {
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *   },
 *   entity_keys = {
 *     "id" = "id"
 *   },
 *   translatable = FALSE,
 *   fieldable = FALSE,
 *   admin_permission = "administer gated content configuration",
 * )
 *
 * @deprecated in openy_gc_auth_custom:8.x-0.2 and is removed from
 *  openy_gc_auth_custom:8.x-1.0 because we switched to drupal user entity.
 * @see https://github.com/fivejars/openy_gated_content/pull/109
 */
class AuthCustomUser extends ContentEntityBase implements AuthCustomUserInterface {

  /**
   * {@inheritdoc}
   */
  public function getVerificationTime() {
    return $this->get('verification_time')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setVerificationTime($timestamp) {
    $this->get('verification_time')->value = $timestamp;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getToken() {
    return $this->get('verification_token')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setToken($token) {
    $this->get('verification_token')->value = $token;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isActive() {
    return $this->get('status')->value == 1;
  }

  /**
   * {@inheritdoc}
   */
  public function activate() {
    $this->get('status')->value = 1;
    $this->get('verification_token')->value = '';
    $this->get('verification_time')->value = NULL;
    return $this;
  }

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

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('User status'))
      ->setDescription(t('Whether the gc_auth_custom_user is verified or blocked.'))
      ->setDefaultValue(FALSE);

    $fields['verification_time'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Verification time'))
      ->setDescription(t('The time at which the verification email was sent.'))
      ->setDefaultValue(NULL);

    $fields['verification_token'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Verification token'))
      ->setDescription(t('The token that gc_auth_custom_user for account activation.'))
      ->setDefaultValue(NULL);

    return $fields;
  }

}
