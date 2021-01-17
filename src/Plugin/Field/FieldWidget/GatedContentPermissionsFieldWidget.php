<?php

namespace Drupal\openy_gated_content\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\openy_gated_content\VirtualYAccessTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

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
class GatedContentPermissionsFieldWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait, VirtualYAccessTrait;

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs widget plugin.
   *
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')
    );
  }

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

    $roles = $this->entityTypeManager->getStorage('user_role')->loadMultiple();
    $select_options = [];
    foreach ($roles as $role_key => $role) {
      if ((strpos($role_key, 'virtual_y') !== FALSE)
        && (!in_array($role_key, $this->getVirtualyEditorRoles()))) {
        $select_options[$role_key] = $role->label();
      }
    }
    $values = isset($items[$delta]->value) ? $items[$delta]->value : NULL;

    $element = [
      '#type' => 'select',
      '#title' => 'Select Virtual Y segment for this content',
      '#options' => $select_options,
      '#multiple' => TRUE,
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
      if (isset($value['_original_delta'])) {
        unset($value['_original_delta']);
      }
      $new_value .= implode(',', $value);
    }
    return $new_value;
  }

}
