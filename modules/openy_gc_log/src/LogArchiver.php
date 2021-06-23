<?php

namespace Drupal\openy_gc_log;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\csv_serialization\Encoder\CsvEncoder;
use Drupal\file\Entity\File;
use Drupal\openy_gc_log\Entity\LogEntityInterface;
use Drupal\user\UserInterface;

/**
 * Log Archiver service.
 */
class LogArchiver {

  use StringTranslationTrait;

  const WORKER_CHUNK_SIZE = 600;

  const BASE_ARCHIVE_PATH = 'vy_logs';
  const VIRTUAL_Y_LOGS = 'virtual-y-logs-';
  const VIRTUAL_Y_ACTIVITY_LOGS = 'virtual-y-activity-logs-';

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
   * Activity Log Entities.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected $activityLogEntities;

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
   * Limit log entities count loaded by current process.
   *
   * @var int
   */
  protected $workerChunkSize = NULL;

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
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The Gated Content Logger.
   *
   * @var \Drupal\openy_gc_log\Logger
   */
  protected $gcLogger;

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
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\openy_gc_log\Logger $gcLogger
   *   The Gated Content Logger.
   */
  public function __construct(
    EntityTypeManager $entityTypeManager,
    LoggerChannel $logger,
    ConfigFactory $configFactory,
    FileSystem $fileSystem,
    Settings $settings,
    ModuleHandlerInterface $module_handler,
    DateFormatterInterface $date_formatter,
    Logger $gcLogger
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->logger = $logger;
    $this->config = $configFactory;
    $this->fileSystem = $fileSystem;
    $this->settings = $settings;
    $this->moduleHandler = $module_handler;
    $this->dateFormatter = $date_formatter;
    $this->gcLogger = $gcLogger;
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
    $filename = $activity_filename = '';
    if (!isset($end)) {
      $tz = $this->config->get('system.date')->get('timezone')['default'];
      $end = (new \DateTime(
        date('Y-m-d', strtotime('last day of last month')),
        new \DateTimeZone($tz)
      ))->setTime(23, 59, 59);
    }
    else {
      $filename = $end->format('Y-m-d-H-i-s');
    }
    $end_time = $end->getTimeStamp();

    if (!isset($start)) {
      $start = clone $end;
      $start->modify('first day of this month 00:00');
    }
    else {
      $filename_end = $start->format('Y-m-d') . '-' . $filename . '.csv.gz';
      $filename = self::VIRTUAL_Y_LOGS . $filename_end;
      $activity_filename = self::VIRTUAL_Y_ACTIVITY_LOGS . $filename_end;
    }
    $start_time = $start->getTimestamp();

    $log_ids = $this->loadLogIdsByDataRange($start_time, $end_time);

    if (empty($log_ids)) {
      return;
    }

    $activity_log_ids = $this->loadActivityLogIdsByDateRange($start_time, $end_time);

    if ($log_ids) {
      $this->loadLogEntities($log_ids);
      $this->prepareLogsForExport($filename);
    }

    if ($activity_log_ids) {
      $this->loadActivityLogEntities($activity_log_ids);
      $this->prepareActivityLogsForExport($activity_filename);
    }

    $this->loadFileEntities();
    $this->writeLogsToFiles();
    $this->saveFileEntities();
    $this->reportToDbLog();

    if ($remove_entities) {
      $this->removeLogsFromDb();
    }
  }

  /**
   * Limit log entities count loaded by current process.
   *
   * @param int $chunk_size
   *   Chunk Size.
   */
  public function setWorkerChunkSize($chunk_size) {
    $this->workerChunkSize = $chunk_size;
  }

  /**
   * Build, run query and return log ids array.
   *
   * @param int $start_time
   *   Timestamp.
   * @param int $end_time
   *   Timestamp.
   *
   * @return array|int
   *   Logs ids.
   */
  protected function loadLogIdsByDataRange($start_time, $end_time) {
    $query = $this->entityTypeManager
      ->getStorage('log_entity')
      ->getQuery()
      ->condition('created', [$start_time, $end_time], 'BETWEEN')
      ->sort('created', 'ASC');

    if ($this->workerChunkSize > 0) {
      $query->range(0, $this->workerChunkSize);
    }

    return $query->execute();
  }

  /**
   * Build, run query and return activity log ids array.
   *
   * @param int $start_time
   *   Timestamp.
   * @param int $end_time
   *   Timestamp.
   *
   * @return array|int
   *   Logs ids array.
   */
  protected function loadActivityLogIdsByDateRange($start_time, $end_time) {
    $query = $this->entityTypeManager->getStorage('log_entity')
      ->getQuery()
      ->condition('event_type', LogEntityInterface::EVENT_TYPE_USER_ACTIVITY)
      ->condition('created', [$start_time, $end_time], 'BETWEEN')
      ->sort('created', 'ASC');

    if ($this->workerChunkSize > 0) {
      $query->range(0, $this->workerChunkSize);
    }

    return $query->execute();
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
   *
   * @param array $log_ids
   *   Log ids.
   */
  protected function loadLogEntities(array $log_ids) {
    $this->logEntities = $this->entityTypeManager
      ->getStorage('log_entity')
      ->loadMultiple($log_ids);

    return $this;
  }

  /**
   * Load activity log entities.
   *
   * @param array $log_ids
   *   Log ids.
   */
  protected function loadActivityLogEntities(array $log_ids) {
    $this->activityLogEntities = $this->entityTypeManager
      ->getStorage('log_entity')
      ->loadMultiple($log_ids);

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
  protected function makeFilename($base, $logEntity) {
    $created = (int) $logEntity->get('created')->value;
    $created_month = date('m', $created);
    $created_year = date('Y', $created);

    return $base . "{$created_year}-{$created_month}.csv.gz";
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
      if (!$log instanceof LogEntityInterface) {
        continue;
      }

      $fileName = $logFileName ?: $this->makeFilename(self::VIRTUAL_Y_LOGS, $log);
      if (!isset($this->preparedLogs[$fileName])) {
        $this->preparedLogs[$fileName] = [
          'Y' => date('Y', $log->get('created')->value),
        ];
      }

      $event_type = $log->get('event_type')->value;
      $entity_type = $log->get('entity_type')->value;
      $entity_bundle = $log->get('entity_bundle')->value;
      $entity_id = $log->get('entity_id')->value;
      $user_data = $log->get('uid')->target_id && ($log->get('uid')->entity instanceof UserInterface) ?
      $log->get('uid')->entity->getEmail() :
      $log->get('email')->value;
      $event_types = [
        'userLoggedIn' => 'Member logged in',
        'entityView' => 'Viewed content',
        'videoPlaybackStarted' => 'Video started',
        'videoPlaybackEnded' => 'Video end reached',
        'userActivity' => 'Member activity detected',
      ];
      $export_row = [
        'Date' => date('m/d/Y - H:i:s', $log->get('created')->value),
        'Member' => $user_data,
        'Event type' => $event_types[$event_type] ?: $event_type,
        'Content type' => $entity_type === 'node' ? 'Ondemand' : 'Live event',
        'Content class' => $entity_bundle === 'gc_video' ? 'vy_video' : $entity_bundle,
        'Content Drupal ID' => $entity_id,
        'Content title' => '',
        'Instructor name' => '',
        'Content creation date' => '',
        'Activity duration' => '',
      ];

      switch ($event_type) {
        case LogEntityInterface::EVENT_TYPE_ENTITY_VIEW:
        case LogEntityInterface::EVENT_TYPE_VIDEO_PLAYBACK_STARTED:
        case LogEntityInterface::EVENT_TYPE_VIDEO_PLAYBACK_ENDED:
          $metadata = $this->gcLogger->getMetadata($log);
          if (empty($metadata)) {
            $metadata = unserialize($log->get('event_metadata')->value, ['allowed_classes' => FALSE]);
          }
          foreach ([
            'entity_title' => $this->t('Content title'),
            'entity_instructor_name' => 'Instructor name',
            'entity_created' => 'Content creation date',
          ] as $key => $export_col) {
            if (!array_key_exists($key, $metadata)) {
              continue 1;
            }
            $export_row[$export_col] = $metadata[$key];
          }
          break;

        case LogEntityInterface::EVENT_TYPE_USER_ACTIVITY:
          $export_row['Activity duration'] = $this->dateFormatter->formatDiff($log->getCreatedTime(), $log->getChangedTime());
          break;
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
   * Prepare activity logs for export.
   *
   * @param string $logFileName
   *   Filename to put all the records into, default -- to create from record's
   *   year and month.
   */
  protected function prepareActivityLogsForExport($logFileName = '') {
    $preparedLogs = [];
    foreach ($this->activityLogEntities as $log) {
      if (!$log instanceof LogEntityInterface) {
        continue;
      }

      $fileName = $logFileName ?: $this->makeFilename(self::VIRTUAL_Y_ACTIVITY_LOGS, $log);
      if (!isset($this->preparedLogs[$fileName])) {
        $this->preparedLogs[$fileName] = [
          'Y' => date('Y', $log->get('created')->value),
        ];
      }

      $user_data = $log->get('uid')->target_id && ($log->get('uid')->entity instanceof UserInterface) ?
        $log->get('uid')->entity->getEmail() :
        $log->get('email')->value;
      $date = date('m/d/Y', $log->get('created')->value);
      $export_row = [
        'Date' => $date,
        'Member' => $user_data,
        'Activity duration' => $log->getChangedTime() - $log->getCreatedTime(),
      ];

      $this->moduleHandler->alter(
        'openy_gc_activity_log_export_row',
        $export_row,
        $log
      );

      $preparedLogs[$fileName][$date][$user_data][] = $export_row;
    }

    foreach ($preparedLogs as $fileName => $logs_lvl1) {
      foreach ($logs_lvl1 as $date => $logs_lvl2) {
        foreach ($logs_lvl2 as $user_data => $logs_lvl3) {
          $row = [];
          foreach ($logs_lvl3 as $logs_lvl4) {
            if (empty($row)) {
              $row = $logs_lvl4;
            }
            else {
              $row['Activity duration'] += $logs_lvl4['Activity duration'];
            }
          }
          $from = new \DateTime();
          $to = (clone $from)->add(\DateInterval::createFromDateString("{$row['Activity duration']} seconds"));
          $row['Activity duration'] = $this->dateFormatter->formatDiff($from->getTimestamp(), $to->getTimestamp());
          $this->preparedLogs[$fileName][] = $row;
        }
      }
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
      'Virtual Y Logs processed into archive: %count entities (%activity_count activity).',
      [
        '%count' => count($this->logEntities),
        '%activity_count' => count($this->activityLogEntities),
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
