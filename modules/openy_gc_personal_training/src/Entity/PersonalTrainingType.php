<?php

namespace Drupal\openy_gc_personal_training\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Personal training type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "personal_training_type",
 *   label = @Translation("Personal training type"),
 *   admin_permission = "administer gated content configuration",
 *   config_prefix = "type",
 *   bundle_of = "personal_training",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *   },
 *   handlers = {
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\BundleEntityFormBase",
 *       "add" = "Drupal\Core\Entity\BundleEntityFormBase",
 *       "edit" = "Drupal\Core\Entity\BundleEntityFormBase",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer site configuration",
 *   links = {
 *     "canonical" = "/admin/structure/personal_training_type/{personal_training_type}",
 *     "add-form" = "/admin/structure/personal_training_type/add",
 *     "edit-form" = "/admin/structure/personal_training_type/{personal_training_type}/edit",
 *     "delete-form" = "/admin/structure/personal_training_type/{personal_training_type}/delete",
 *     "collection" = "/admin/structure/personal_training_type",
 *   }
 * )
 */
class PersonalTrainingType extends ConfigEntityBundleBase {

  /**
   * The machine name of this Personal training type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the Personal training type.
   *
   * @var string
   */
  protected $label;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

}
