<?php

namespace Drupal\openy_gc_auth\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Identity Provider Edit Form.
 */
class GCIdentityProviderEditForm extends FormBase {

  /**
   * The Identity Provider plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $identityProviderManager;

  /**
   * The plugin instance.
   *
   * @var \Drupal\openy_gc_auth\GCIdentityProviderInterface
   */
  protected $identityProviderInstance = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct(PluginManagerInterface $gc_identity_provider_manager) {
    $this->identityProviderManager = $gc_identity_provider_manager;
    $plugin_id = $this->getRouteMatch()->getParameter('type');
    $plugin_definition = $this->identityProviderManager->getDefinition($plugin_id, FALSE);
    if ($plugin_definition) {
      $this->identityProviderInstance = $this->identityProviderManager->createInstance($plugin_id);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.gc_identity_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gc_identity_provider_edit';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state):array {
    if (!$this->identityProviderInstance) {
      $form['no_results'] = [
        '#markup' => $this->t('Plugin instance not found.'),
      ];
      return $form;
    }

    $form['settings'] = $this->identityProviderInstance->buildConfigurationForm($form, $form_state);
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
    $this->identityProviderInstance->validateConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state):void {
    $this->identityProviderInstance->submitConfigurationForm($form, $form_state);
    $this->messenger()->addStatus($this->t('@label has been saved.', [
      '@label' => $this->identityProviderInstance->getLabel(),
    ]));
    $form_state->setRedirect('openy_gc_auth.settings');
  }

}
