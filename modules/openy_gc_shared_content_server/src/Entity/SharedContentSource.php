<?php

namespace Drupal\openy_gc_shared_content_server\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Shared content source entity.
 *
 * @ingroup openy_gc_shared_content_server
 *
 * @ContentEntityType(
 *   id = "shared_content_source",
 *   label = @Translation("Shared content source"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\openy_gc_shared_content_server\SharedContentSourceListBuilder",
 *     "views_data" = "Drupal\openy_gc_shared_content_server\Entity\SharedContentSourceViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\openy_gc_shared_content_server\Form\SharedContentSourceForm",
 *       "add" = "Drupal\openy_gc_shared_content_server\Form\SharedContentSourceForm",
 *       "edit" = "Drupal\openy_gc_shared_content_server\Form\SharedContentSourceForm",
 *       "delete" = "Drupal\openy_gc_shared_content_server\Form\SharedContentSourceDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\openy_gc_shared_content_server\SharedContentSourceHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\openy_gc_shared_content_server\SharedContentSourceAccessControlHandler",
 *   },
 *   base_table = "shared_content_source",
 *   translatable = FALSE,
 *   admin_permission = "administer shared content source entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "url" = "url"
 *   },
 *   links = {
 *     "canonical" = "/admin/virtual-y/shared-content/shared_content_source/{shared_content_source}",
 *     "add-form" = "/admin/virtual-y/shared-content/shared_content_source/add",
 *     "edit-form" = "/admin/virtual-y/shared-content/shared_content_source/{shared_content_source}/edit",
 *     "delete-form" = "/admin/virtual-y/shared-content/shared_content_source/{shared_content_source}/delete",
 *     "collection" = "/admin/virtual-y/shared-content/shared_content_source",
 *   },
 *   field_ui_base_route = "shared_content_source.settings"
 * )
 */
class SharedContentSource extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl() {
    return $this->get('url')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setUrl($url) {
    $this->set('url', $url);
    return $this;
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

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Shared content source entity.'))
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

    $fields['url'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Url'))
      ->setDescription(t('The url of the Shared content source entity.'))
      ->setSettings([
        'max_length' => 255,
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

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    return $fields;
  }

}
