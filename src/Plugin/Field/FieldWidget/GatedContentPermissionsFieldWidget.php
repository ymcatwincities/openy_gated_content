<?php

namespace Drupal\openy_gated_content\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\Role;

/**
 * Plugin implementation of the Gated Content permissions widget.
 *
 * @FieldWidget(
 *   id = "virtual_y_roles_select",
 *   label = @Translation("Virtual Y roles selector"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class GatedContentPermissionsFieldWidget extends WidgetBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [$this->t('Virtual Y group selector')];
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $roles = Role::loadMultiple();
    $select_options = [];
    foreach ($roles as $role_key => $role) {
      if ((strpos($role_key, 'virtual_y') !== FALSE)
        && ($role_key!== 'virtual_ymca_editor')) {
        $select_options[$role_key] = $role->label();
      }
    }
    $values = isset($items[$delta]->value) ? $items[$delta]->value : NULL;

    $element = [
      '#type' => 'select',
      '#title' => 'Select Virtual Y segment for this content',
      '#options' => $select_options,
      '#multiple' => TRUE,
      '#element_validate' => [get_class($this), 'validate'],
      //'#value_callback' => [get_class($this), 'value'],
      '#default_value' => !empty($values) ? explode(',', $values) : NULL,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {

    $new_value = '';
    foreach ($values as $value) {
      $new_value .= implode(',', $value);
    }
    return $new_value;
  }

}
