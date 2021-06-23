<?php

namespace Drupal\openy_gc_personal_training\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Personal Training Provider Edit Form.
 */
class PersonalTrainingProviderEditForm extends FormBase {

  /**
   * The Personal Training Provider plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $personalTrainingProviderManager;

  /**
   * The plugin instance.
   *
   * @var \Drupal\openy_gc_personal_training\PersonalTrainingProviderInterface
   */
  protected $pluginInstance = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct(PluginManagerInterface $personal_training_provider_manager) {
    $this->personalTrainingProviderManager = $personal_training_provider_manager;
    $plugin_id = $this->getRouteMatch()->getParameter('type');
    $plugin_definition = $this->personalTrainingProviderManager->getDefinition($plugin_id, FALSE);
    if ($plugin_definition) {
      $this->pluginInstance = $this->personalTrainingProviderManager->createInstance($plugin_id);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.personal_training_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gc_personal_training_provider_edit';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state):array {
    if (!$this->pluginInstance) {
      $form['no_results'] = [
        '#markup' => $this->t('Plugin instance not found.'),
      ];
      return $form;
    }

    $form['settings'] = $this->pluginInstance->buildConfigurationForm($form, $form_state);
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state):void {
    $this->pluginInstance->validateConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state):void {
    $this->pluginInstance->submitConfigurationForm($form, $form_state);
    $this->messenger()->addStatus($this->t('@label has been saved.', [
      '@label' => $this->pluginInstance->getLabel(),
    ]));
    $form_state->setRedirect('personal_training.settings');
  }

}
