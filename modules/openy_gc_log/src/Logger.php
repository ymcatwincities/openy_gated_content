<?php

namespace Drupal\openy_gc_log;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\openy_gc_log\Entity\LogEntity;
use Drupal\openy_gc_log\Entity\LogEntityInterface;
use Drupal\user\UserInterface;

/**
 * Service logger.
 */
class Logger {

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  private $logger;

  /**
   * The entity type targeted by this resource.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Logger constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannel $logger
   *   LoggerChannel.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(
    LoggerChannel $logger,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory
  ) {
    $this->logger = $logger;
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * Create log_entity.
   *
   * @param array $params
   *   Array with parameters.
   *
   * @return bool|Entity\LogEntity
   *   Return false or log_entity.
   */
  public function addLog(array $params) {
    try {
      $log = new LogEntity([], 'log_entity');
      foreach ($params as $param => $value) {
        $log->set($param, $value);
      }
      $log->set('event_metadata', serialize($this->getMetadata($log)));
      $log->setCreatedTime(time());
      $log->save();
      return $log;
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      return FALSE;
    }
  }

  /**
   * Create log_entity.
   *
   * @param int $user_id
   *   The user id which activity should be tracked.
   *
   * @return bool|Entity\LogEntity
   *   Return false or log_entity.
   */
  public function trackActivity(int $user_id) {
    try {
      $interval = $this->configFactory->get('openy_gc_log.settings')->get('activity_granularity_interval');
      $query = $this->entityTypeManager->getStorage('log_entity')->getQuery();
      $query->condition('uid', $user_id);
      $query->condition('event_type', LogEntityInterface::EVENT_TYPE_USER_ACTIVITY);
      $query->condition('changed', (new \DateTime("-$interval seconds"))->getTimestamp(), '>=');
      $query->sort('changed', 'DESC');
      $query->range(0, 1);
      $results = $query->execute();

      if (count($results) > 0) {
        $log_record = $this->entityTypeManager->getStorage('log_entity')->load(reset($results));
      }
      else {
        $user = $this->entityTypeManager->getStorage('user')->load($user_id);
        $log_record = new LogEntity([], 'log_entity');
        $log_record->set('uid', $user_id);
        $log_record->set('email', $user->getEmail());
        $log_record->set('event_type', LogEntityInterface::EVENT_TYPE_USER_ACTIVITY);
        $log_record->setCreatedTime(time());
      }
      $log_record->setChangedTime(time());
      $log_record->save();

      return $log_record;
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(LogEntityInterface $log) {
    $metadata = [];
    $entity_type = $log->get('entity_type')->value;
    $entity_type_ids = [
      'node' => 'node',
      'series' => 'eventinstance',
      'personal_training' => 'personal_training',
    ];
    if (!array_key_exists($entity_type, $entity_type_ids)) {
      return $metadata;
    }
    $entity_type_id = $entity_type_ids[$entity_type];
    $entity_id = $log->get('entity_id')->value;
    if (empty($entity_id)) {
      return $metadata;
    }
    $entity = $this->entityTypeManager->getStorage($entity_type_id)
      ->load($entity_id);
    if (!$entity instanceof EntityInterface) {
      return $metadata;
    }

    switch ($entity_type_id) {
      case 'node':
        $metadata['entity_title'] = $entity->label();
        if ($log->get('entity_bundle')->value === 'gc_video') {
          $metadata['entity_instructor_name'] = $entity->get('field_gc_video_instructor')->value;
        }
        break;
      case 'eventinstance':
        $metadata['entity_title'] = $entity->get('field_ls_title')->value ?: $entity->get('title')->value;
        $metadata['entity_instructor_name'] = $entity->get('field_ls_host_name')->value ?: $entity->get('host_name')->value;
        break;
      case 'personal_training':
        $metadata['entity_title'] = $entity->get('title')->value;
        $instructor = $entity->get('instructor_id')->entity;
        if ($instructor instanceof UserInterface) {
          $metadata['entity_instructor_name'] = $instructor->getEmail();
        }
        break;
    }
    $metadata['entity_created'] = date('m/d/Y - H:i:s', $entity->getCreatedTime());

    return $metadata;
  }

}
