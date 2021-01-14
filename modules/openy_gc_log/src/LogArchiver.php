<?php

namespace Drupal\openy_gc_log;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Site\Settings;
use Drupal\csv_serialization\Encoder\CsvEncoder;
use Drupal\file\Entity\File;
use Drupal\openy_gc_log\Entity\LogEntityInterface;

/**
 * Log Archiver service.
 */
class LogArchiver {

  const WORKER_CHUNK_SIZE = 600;

  const BASE_ARCHIVE_PATH = 'vy_logs';

  /**
   * Log Ids.
   *
   * @var array
   */
  protected $logIds;

  /**
   * Log Entities.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected $logEntities;

  /**
   * Prepared Logs.
   *
   * @var array
   */
  protected $preparedLogs;

  /**
   * File Entities.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected $fileEntities;

  /**
   * Entity Type Manager to work with.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  private $entityTypeManager;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  private $logger;


  /**
   * Configs.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  private $config;

  /**
   * FileSystem.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * Site Settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * LogArchiver constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   EntityTypeManager.
   * @param \Drupal\Core\Logger\LoggerChannel $logger
   *   LoggerChannel.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   ConfigFactory.
   * @param \Drupal\Core\File\FileSystem $fileSystem
   *   FileSystem.
   * @param \Drupal\Core\Site\Settings $settings
   *   Settings.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(
    EntityTypeManager $entityTypeManager,
    LoggerChannel $logger,
    ConfigFactory $configFactory,
    FileSystem $fileSystem,
    Settings $settings,
    ModuleHandlerInterface $module_handler
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->logger = $logger;
    $this->config = $configFactory;
    $this->fileSystem = $fileSystem;
    $this->settings = $settings;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Archiving entities functionality.
   *
   * @param \DateTime|null $start
   *   (optional) DateTime value for Start time, defaults to the month start.
   * @param \DateTime|null $end
   *   (optional) DateTime value for End time, defaults to the month end.
   * @param bool $remove_entities
   *   (optional) Should an entities be removed after creating an export file.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function archive($start = NULL, $end = NULL, $remove_entities = TRUE) {
    $filename = '';
    if (!isset($end)) {
      $tz = $this->config->get('system.date')->get('timezone')['default'];
      $end = (new \DateTime(
        date('Y-m-d', strtotime('last day of last month')),
        new \DateTimeZone($tz)
      ))->setTime(23, 59, 59);
    }
    else {
      $filename = $end->format('Y_m_d_H_i_s');
    }
    $end_time = $end->getTimeStamp();

    if (!isset($start)) {
      $start = clone $end;
      $start->modify('first day of this month 00:00');
    }
    else {
      $filename = 'virtual-y-logs-' . $start->format('Y_m_d') . '-' . $filename . '.csv.gz';
    }
    $start_time = $start->getTimestamp();

    $query = $this->entityTypeManager->getStorage('log_entity')
      ->getQuery()
      ->condition('created', [$start_time, $end_time], 'BETWEEN')
      ->sort('created', 'ASC');
    if (!isset($start) && !isset($end)) {
      $query->range(0, self::WORKER_CHUNK_SIZE);
    }
    $log_ids = $query->execute();

    if (empty($log_ids)) {
      return;
    }

    $this->setLogIds($log_ids)
      ->loadLogEntities()
      ->prepareLogsForExport($filename)
      ->loadFileEntities()
      ->writeLogsToFiles()
      ->saveFileEntities()
      ->reportToDbLog();

    if ($remove_entities) {
      $this->removeLogsFromDb();
    }
  }

  /**
   * Set log ids.
   */
  protected function setLogIds($logIds) {
    $this->logIds = $logIds;

    return $this;
  }

  /**
   * Load log entities.
   */
  protected function loadLogEntities() {
    $this->logEntities = $this->entityTypeManager
      ->getStorage('log_entity')
      ->loadMultiple($this->logIds);

    return $this;
  }

  /**
   * Check is private dir enabled.
   */
  protected function isPrivateDirEnabled() {
    return $this->settings::get('file_private_path', FALSE);
  }

  /**
   * Prepare directory for logs.
   */
  protected function prepareYearDirectory($dir) {
    if ($this->isPrivateDirEnabled()) {
      $dir = 'private://' . self::BASE_ARCHIVE_PATH . DIRECTORY_SEPARATOR . $dir;
    }
    else {
      $salt = $this->settings::getHashSalt();
      $dir = 'public://' . self::BASE_ARCHIVE_PATH . DIRECTORY_SEPARATOR . $salt .
        DIRECTORY_SEPARATOR . $dir;
    }
    if (!$this->fileSystem->prepareDirectory($dir,
      FileSystem::CREATE_DIRECTORY)) {
      throw new \RuntimeException(sprintf('Can not create directory "%s"', $dir));
    }

    return $dir;
  }

  /**
   * Make filename.
   */
  protected function makeFilename($logEntity) {
    $created = (int) $logEntity->get('created')->value;
    $created_month = date('m', $created);
    $created_year = date('Y', $created);

    return "virtual-y-logs-{$created_year}-{$created_month}.csv.gz";
  }

  /**
   * Prepare logs for export.
   *
   * @param string $logFileName
   *   Filename to put all the records into, default -- to create from record's
   *   year and month.
   */
  protected function prepareLogsForExport($logFileName = '') {
    foreach ($this->logEntities as $log) {
      $fileName = $logFileName ?: $this->makeFilename($log);
      if (!isset($this->preparedLogs[$fileName])) {
        $this->preparedLogs[$fileName] = [
          'Y' => date('Y', $log->get('created')->value),
        ];
      }

      $event_type = $log->get('event_type')->value;
      $entity_type = $log->get('entity_type')->value;
      $entity_bundle = $log->get('entity_bundle')->value;
      $entity_id = $log->get('entity_id')->value;
      $export_row = [
        'created' => date('m/d/Y - H:i:s', $log->get('created')->value),
        'user' => $log->get('uid')->target_id ? $log->get('uid')->entity->getEmail() : '',
        'event_type' => $event_type,
        'entity_type' => $entity_type,
        'entity_bundle' => $entity_bundle,
        'entity_id' => $entity_id,
        'entity_title' => '',
        'entity_instructor_name' => '',
        'entity_created' => '',
      ];

      if (in_array($event_type, [
        LogEntityInterface::EVENT_TYPE_ENTITY_VIEW,
        LogEntityInterface::EVENT_TYPE_VIDEO_PLAYBACK_STARTED,
        LogEntityInterface::EVENT_TYPE_VIDEO_PLAYBACK_ENDED,
      ])) {
        $entity_type_id = $entity_type === 'node' ? 'node' : 'eventinstance';
        $entity = $this->entityTypeManager->getStorage($entity_type_id)
          ->load($entity_id);
        $export_row['entity_title'] = is_null($entity) ? $entity_id :
          ($entity_type === 'node' ? $entity->label() :
          ($entity->get('field_ls_title')->value ? $entity->get('field_ls_title')->value : $entity->get('title')->value));
        $export_row['entity_instructor_name'] = is_null($entity) ? '' :
          ($entity_type === 'node' ?
          ($entity_bundle === 'gc_video' ? $entity->get('field_gc_video_instructor')->value : '') :
          ($entity->get('field_ls_host_name')->value ? $entity->get('field_ls_host_name')->value : $entity->get('host_name')->value));
        $export_row['entity_created'] = is_null($entity) ? '' : date('m/d/Y - H:i:s', $entity->getCreatedTime());
      }

      $this->moduleHandler->alter(
        'openy_gc_log_export_row',
        $export_row,
        $log
      );

      $this->preparedLogs[$fileName][] = $export_row;
    }

    return $this;
  }

  /**
   * Create new file entity.
   */
  protected function createNewFileEntity($fileName) {
    $fileYear = $this->preparedLogs[$fileName]['Y'];
    $yearDir = $this->prepareYearDirectory($fileYear);
    $file = File::create();
    $file->setFilename($fileName);
    if ($this->isPrivateDirEnabled()) {
      $file->setFileUri($yearDir . DIRECTORY_SEPARATOR . $fileName);
    }
    else {
      $file->setFileUri($yearDir . DIRECTORY_SEPARATOR . md5(mt_rand()) . '-' . $fileName);
    }
    $file->setMimeType('application/x-gzip');
    $file->setPermanent();
    return $file;
  }

  /**
   * Load file entities.
   */
  protected function loadFileEntities() {
    $file_ids = $this->entityTypeManager
      ->getStorage('file')
      ->getQuery()
      ->condition('filename', array_keys($this->preparedLogs), 'in')
      ->execute();

    $file_entities = $this->entityTypeManager
      ->getStorage('file')->loadMultiple($file_ids);

    $this->fileEntities = [];
    foreach ($file_entities as $file_entity) {
      $this->fileEntities[$file_entity->getFilename()] = $file_entity;
    }

    foreach (array_keys($this->preparedLogs) as $fileName) {
      if (!array_key_exists($fileName, $this->fileEntities)) {
        $this->fileEntities[$fileName] = $this->createNewFileEntity($fileName);
      }
      unset($this->preparedLogs[$fileName]['Y']);
    }

    return $this;
  }

  /**
   * Write logs to files.
   */
  protected function writeLogsToFiles() {
    foreach ($this->preparedLogs as $fileName => $logs) {
      $fileEntity = $this->fileEntities[$fileName];

      $csvEncoder = new CsvEncoder();
      $csvEncoder->setSettings([
        'delimiter' => ",",
        'enclosure' => '"',
        'escape_char' => "\\",
        'encoding' => 'utf8',
        'strip_tags' => TRUE,
        'trim' => TRUE,
        'output_header' => $fileEntity->isNew(),
      ]);

      $fileContents = $csvEncoder->encode($logs, 'csv');

      $filePointer = gzopen($fileEntity->getFileUri(), 'a3');
      gzwrite($filePointer, "\n" . $fileContents);
      gzclose($filePointer);
    }

    return $this;
  }

  /**
   * Save file entities.
   */
  protected function saveFileEntities() {
    foreach ($this->fileEntities as $fileEntity) {
      $fileEntity->save();
    }

    return $this;
  }

  /**
   * Report to DbLog.
   */
  protected function reportToDbLog() {
    $this->logger->debug(
      'Virtual Y Logs processed into archive: %count entities.',
      [
        '%count' => count($this->logIds),
      ]
    );

    return $this;
  }

  /**
   * Remove logs from db.
   */
  protected function removeLogsFromDb() {
    $this->entityTypeManager
      ->getStorage('log_entity')
      ->delete($this->logEntities);

    return $this;
  }

}
