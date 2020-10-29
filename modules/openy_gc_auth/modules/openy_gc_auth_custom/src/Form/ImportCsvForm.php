<?php

namespace Drupal\openy_gc_auth_custom\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\FileUsage\FileUsageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Import form.
 */
class ImportCsvForm extends FormBase {

  const TEMP_DIR = 'private://gc_auth/temp';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The file usage service.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs the ImportCsvForm.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\file\FileUsage\FileUsageInterface $file_usage
   *   The file usage service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FileUsageInterface $file_usage, FileSystemInterface $file_system) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fileUsage = $file_usage;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('file.usage'),
      $container->get('file_system')
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
    $file_id = NULL;
    $migration = $this->entityTypeManager
      ->getStorage('migration')
      ->load('gc_auth_custom_users');
    if ($migration) {
      $path = $migration->get('source')['path'];
      $files = $this->entityTypeManager->getStorage('file')
        ->loadByProperties(['uri' => $path]);
      if (!empty($files)) {
        $file_id = reset($files)->id();
      }
    }
    $form_state->set('csv_file_id', $file_id);
    $form['migrate']['csv_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('CSV file'),
      '#description' => $this->t('CSV file to import.'),
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
      ],
      '#upload_location' => self::TEMP_DIR,
      '#default_value' => $file_id ? [$file_id] : NULL,
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
    $migration = $this->entityTypeManager
      ->getStorage('migration')
      ->load('gc_auth_custom_users');
    if (!$migration) {
      $form_state->setErrorByName('csv_file', $this->t('Migration "gc_auth_custom_users" with file destination settings not found.'));
    }
    $file_path = $migration->get('source')['path'];
    $dir = substr($file_path, 0, strrpos($file_path, '/', -1));
    $url = Url::fromRoute('openy_gc_auth.provider.edit', ['type' => 'custom']);
    $form_state->setRedirectUrl($url);

    $fid = $form_state->getValue(['csv_file', 0]);
    $csv_file_id = $form_state->get('csv_file_id');
    if ($fid == $csv_file_id) {
      return;
    }

    $file_storage = $this->entityTypeManager->getStorage('file');
    // Handle old file.
    if ($csv_file_id && $file = $file_storage->load($csv_file_id)) {
      $this->fileUsage->delete($file, 'openy_gc_auth_custom', 'migration', 'gc_auth_custom_users');
    }
    // Handle new file.
    if ($fid && $file = $file_storage->load($fid)) {
      // Rename file.
      $this->fileSystem->prepareDirectory($dir, FileSystemInterface::CREATE_DIRECTORY);
      $file = file_move($file, $file_path, FileSystemInterface::EXISTS_REPLACE);
      $this->fileUsage->add($file, 'openy_gc_auth_custom', 'migration', 'gc_auth_custom_users');
    }
  }

}
