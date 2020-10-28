<?php

namespace Drupal\openy_gc_auth_custom\Form;

use Drupal\Core\Link;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\openy_gc_auth_custom\ImportCsvBatch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drupal\migrate_plus\Entity\MigrationGroup;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Import form.
 */
class ImportCsvForm extends FormBase {

  /**
   * The migration plugin manager.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface
   */
  protected $pluginManager;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs the ImportCsvForm.
   *
   * @param \Drupal\migrate\Plugin\MigrationPluginManagerInterface $plugin_manager
   *   The migration plugin manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(MigrationPluginManagerInterface $plugin_manager, MessengerInterface $messenger) {
    $this->pluginManager = $plugin_manager;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.migration'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openy_gc_auth_migrate_import_csv_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $migration_groups = MigrationGroup::loadMultiple();
    $form['info'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Please verify migration group settings by editing it at @link.', [
        '@link' => Link::createFromRoute(
          $this->t('Migration groups'),
          'entity.migration_group.edit_form',
          ['migration_group' => 'gc_auth'],
          ['attributes' => ['target' => '_blank']])->toString(),
      ]),
    ];

    $options = [];
    foreach ($migration_groups as $migration_group) {
      $shared_config = $migration_group->get('shared_configuration');
      if ($shared_config && in_array($shared_config['source']['plugin'], ['csv', 'csv_limit'])) {
        $options[$migration_group->id()] = $migration_group->label();
      }
    }
    $form['migration_group'] = [
      '#type' => 'select',
      '#title' => $this->t('Select migration group to run'),
      '#options' => $options,
    ];
    $form['count'] = [
      '#type' => 'select',
      '#title' => $this->t('Rows limit per batch step'),
      '#options' => array_combine([10, 20, 50, 100], [10, 20, 50, 100]),
      '#default_value' => 10,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $migration_group_id = $form_state->getValue('migration_group');
    $count = (int) $form_state->getValue('count');
    $migrations = $this->pluginManager->createInstances([]);
    ImportCsvBatch::buildBatch($migrations, $migration_group_id, $count);
  }

}
