<?php

namespace Drupal\openy_gc_personal_training;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface;

/**
 * Personal Trainings Series Manager.
 */
class PersonalTrainingSeriesManager implements PersonalTrainingSeriesManagerInterface {

  use DependencySerializationTrait;
  use StringTranslationTrait;

  const BATCH_LIMIT = 20;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Batch Builder.
   *
   * @var \Drupal\Core\Batch\BatchBuilder
   */
  protected $batchBuilder;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Configs.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  private $config;

  /**
   * The module list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleList;

  /**
   * PersonalTrainingSeriesManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   ConfigFactory.
   * @param \Drupal\Core\Extension\ModuleExtensionList $module_list
   *   The module list.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    MessengerInterface $messenger,
    ConfigFactory $configFactory,
    ModuleExtensionList $module_list
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->batchBuilder = new BatchBuilder();
    $this->messenger = $messenger;
    $this->config = $configFactory;
    $this->moduleList = $module_list;
  }

  /**
   * Provides the array of the date ranges, which should be excluded.
   *
   * @param \Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface $series
   *   Personal training series entity, to take data from.
   *
   * @return array
   *   Array of the date ranges.
   */
  protected function getExclusionsDateRanges(PersonalTrainingInterface $series): array {
    $exclusions_data = $series->field_exclusions->getValue();
    foreach ($exclusions_data as &$exclusion) {
      $exclusion['start'] = DrupalDateTime::createFromFormat(
        DateTimeItemInterface::DATETIME_STORAGE_FORMAT,
        $exclusion['value'],
        DateTimeItemInterface::STORAGE_TIMEZONE)
        ->getTimestamp();
      $exclusion['end'] = DrupalDateTime::createFromFormat(
        DateTimeItemInterface::DATETIME_STORAGE_FORMAT,
        $exclusion['end_value'],
        DateTimeItemInterface::STORAGE_TIMEZONE)
        ->getTimestamp();
    }
    return $exclusions_data;
  }

  /**
   * Provides array of the personal trainings dates for the specified series.
   *
   * @param \Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface $series
   *   Personal training series entity, to take data from.
   *
   * @return array
   *   Array of the date ranges.
   */
  public function getTrainingsDates(PersonalTrainingInterface $series): array {
    $date_ranges = [];
    $timezone = new \DateTimeZone($this->config->get('system.date')->get('timezone')['default']);

    $exclusions = $this->getExclusionsDateRanges($series);

    foreach ($series->field_schedule as $schedule) {
      $occurrences = $schedule->getHelper()->getOccurrences();
      if (empty($occurrences)) {
        continue;
      }
      foreach ($occurrences as $occurrence) {
        /** @var \DateTimeInterface $end */
        $end = $occurrence->getEnd();
        $end->setTimezone($timezone);
        if ($end->getTimestamp() < time()) {
          continue;
        }
        /** @var \DateTimeInterface $start */
        $start = $occurrence->getStart();
        $start->setTimezone($timezone);

        $exclude = FALSE;
        foreach ($exclusions as $exclusion) {
          if ($start->getTimestamp() <= $exclusion['end'] && $end->getTimestamp() >= $exclusion['start']) {
            $exclude = TRUE;
            break;
          }
        }

        if (!$exclude) {
          $date_ranges[] = [
            'start' => $start,
            'end' => $end,
          ];
        }
      }
    }

    usort($date_ranges, function ($a, $b) {
      return $a['start']->getTimestamp() - $b['start']->getTimestamp();
    });

    return $date_ranges;
  }

  /**
   * Creates personal_training entity and fills its fields from the series.
   *
   * @param \Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface $series
   *   Entity type manager.
   * @param array $date_range
   *   Array, containing data for the personal training date field.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createTrainingForSeries(PersonalTrainingInterface $series, array $date_range): void {
    $values = $series->toArray();
    $keys_to_exclude = ['id', 'uuid', 'field_schedule', 'field_exclusions'];
    $values = array_diff_key($values, array_flip($keys_to_exclude));
    $values['type'] = 'personal_training';
    $values['field_parent'] = ['target_id' => $series->id()];

    $start = $date_range['start'];
    $start->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    $end = $date_range['end'];
    $end->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    $values['date'] = [
      'value' => $start->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
      'end_value' => $end->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT),
    ];

    $this->entityTypeManager
      ->getStorage('personal_training')
      ->create($values)
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  public function generateItemsForSeries(int $series_id, array &$context): void {
    $series = $this->entityTypeManager
      ->getStorage('personal_training')
      ->load($series_id);
    if (!($series instanceof PersonalTrainingInterface)) {
      return;
    }

    $trainings_dates = $this->getTrainingsDates($series);

    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = count($trainings_dates);
      $context['results']['op'] = $this->t('created');
    }

    // Process all the operations right away if it's in Drush context.
    if (PHP_SAPI === 'cli') {
      $chunk = $trainings_dates;
    }
    else {
      $chunk = array_slice($trainings_dates, $context['sandbox']['progress'], $context['sandbox']['progress'] + self::BATCH_LIMIT);
    }

    foreach ($chunk as $date_range) {
      $this->createTrainingForSeries($series, $date_range);
      $context['sandbox']['progress']++;
      $context['message'] = $this->t('Creating PTs :progress of :count', [
        ':progress' => $context['sandbox']['progress'],
        ':count' => $context['sandbox']['max'],
      ]);
      $context['results']['processed'] = $context['sandbox']['progress'];
    }

    $context['finished'] = empty($context['sandbox']['max']) ? 1 : ($context['sandbox']['progress'] / $context['sandbox']['max']);
  }

  /**
   * {@inheritdoc}
   */
  public function updateItemsOfSeries(int $series_id, array &$context): void {
    $storage = $this->entityTypeManager->getStorage('personal_training');
    $series = $storage->load($series_id);
    if (!($series instanceof PersonalTrainingInterface)) {
      return;
    }
    $values = $series->toArray();
    $keys_to_exclude = [
      'id',
      'uuid',
      'field_schedule',
      'field_exclusions',
      'type',
      'date',
      'uid',
    ];
    $values = array_diff_key($values, array_flip($keys_to_exclude));

    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current'] = 0;
      $context['sandbox']['max'] = $storage->getQuery()
        ->condition('type', 'personal_training')
        ->condition('field_parent', $series_id)
        ->count()
        ->execute();
      $context['results']['op'] = $this->t('updated');
    }

    $ids = $storage->getQuery()
      ->condition('type', 'personal_training')
      ->condition('field_parent', $series_id)
      ->condition('id', $context['sandbox']['current'], '>')
      ->range(0, self::BATCH_LIMIT)
      ->sort('id')
      ->execute();
    if (!$ids) {
      return;
    }
    $items = $storage->loadMultiple($ids);
    foreach ($items as $item) {
      $context['sandbox']['current'] = $item->id();
      foreach ($values as $key => $value) {
        $item->set($key, $value);
      }
      $item->save();
      $context['sandbox']['progress']++;
      $context['message'] = $this->t('Updating PTs :progress of :count', [
        ':progress' => $context['sandbox']['progress'],
        ':count' => $context['sandbox']['max'],
      ]);
      $context['results']['processed'] = $context['sandbox']['progress'];
    }

    $context['finished'] = empty($context['sandbox']['max']) ? 1 : ($context['sandbox']['progress'] / $context['sandbox']['max']);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteItemsOfSeries(int $series_id, array &$context): void {
    $storage = $this->entityTypeManager->getStorage('personal_training');
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current'] = 0;
      $context['sandbox']['max'] = $storage->getQuery()
        ->condition('type', 'personal_training')
        ->condition('field_parent', $series_id)
        ->count()
        ->execute();
      $context['results']['op'] = $this->t('deleted');
    }

    $ids = $storage->getQuery()
      ->condition('type', 'personal_training')
      ->condition('field_parent', $series_id)
      ->condition('id', $context['sandbox']['current'], '>')
      ->range(0, self::BATCH_LIMIT)
      ->sort('id')
      ->execute();
    if (!$ids) {
      return;
    }
    $items = $storage->loadMultiple($ids);
    foreach ($items as $item) {
      $context['sandbox']['current'] = $item->id();
      $item->delete();
      $context['sandbox']['progress']++;
      $context['message'] = $this->t('Deleting PTs :progress of :count', [
        ':progress' => $context['sandbox']['progress'],
        ':count' => $context['sandbox']['max'],
      ]);
      $context['results']['processed'] = $context['sandbox']['progress'];
    }

    $context['finished'] = empty($context['sandbox']['max']) ? 1 : ($context['sandbox']['progress'] / $context['sandbox']['max']);
  }

  /**
   * {@inheritdoc}
   */
  public function cancelItemsOfSeries(int $series_id, array &$context): void {
    $storage = $this->entityTypeManager->getStorage('personal_training');
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current'] = 0;
      $context['sandbox']['max'] = $storage->getQuery()
        ->condition('type', 'personal_training')
        ->condition('field_parent', $series_id)
        ->count()
        ->execute();
      $context['results']['op'] = $this->t('deleted');
    }

    $ids = $storage->getQuery()
      ->condition('type', 'personal_training')
      ->condition('field_parent', $series_id)
      ->condition('id', $context['sandbox']['current'], '>')
      ->range(0, self::BATCH_LIMIT)
      ->sort('id')
      ->execute();
    if (!$ids) {
      return;
    }
    $items = $storage->loadMultiple($ids);
    /** @var \Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface $item */
    foreach ($items as $item) {
      $context['sandbox']['current'] = $item->id();
      $context['sandbox']['progress']++;
      $item->getState()->applyTransitionById('cancel');
      $item->save();
      $context['message'] = $this->t('Canceling PTs :progress of :count', [
        ':progress' => $context['sandbox']['progress'],
        ':count' => $context['sandbox']['max'],
      ]);
      $context['results']['processed'] = $context['sandbox']['progress'];
    }

    $context['finished'] = empty($context['sandbox']['max']) ? 1 : ($context['sandbox']['progress'] / $context['sandbox']['max']);
  }

  /**
   * {@inheritdoc}
   */
  public function buildBatch(int $id, array $methods): void {
    if (PHP_SAPI === 'cli') {
      // Process all the operations right away if it's in Drush context.
      $context = [];
      foreach ($methods as $method) {
        $this->{$method}($id, $context);
      }
    }
    else {
      // Process operations in batch if in web context.
      $this->batchBuilder
        ->setTitle($this->t('Processing'))
        ->setInitMessage($this->t('Initializing.'))
        ->setProgressMessage($this->t('Completed @current of @total.'))
        ->setErrorMessage($this->t('An error has occurred.'))
        ->setProgressive(TRUE);
      $this->batchBuilder->setFile($this->moduleList->getPath('openy_gc_personal_training') . '/src/PersonalTrainingSeriesManager.php');
      foreach ($methods as $method) {
        $this->batchBuilder->addOperation([$this, $method], [$id]);
      }
      $this->batchBuilder->setFinishCallback([$this, 'batchFinished']);
      batch_set($this->batchBuilder->toArray());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function batchFinished($success, $results, $operations): void {
    $processed = $results['processed'] ?? 0;
    $op = $results['op'] ?? 'processed';
    $this->messenger->addStatus($this->t(':count items were :op!', [
      ':count' => $processed,
      ':op' => $op,
    ]));
  }

}
