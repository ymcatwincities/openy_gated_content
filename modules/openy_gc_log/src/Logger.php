<?php

namespace Drupal\openy_gc_log;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\openy_gc_log\Entity\LogEntity;

/**
 * Logger service.
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
   * LogArchiver constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannel $logger
   *   LoggerChannel.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(LoggerChannel $logger, EntityTypeManagerInterface $entity_type_manager) {
    $this->logger = $logger;
    $this->entityTypeManager = $entity_type_manager;
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
      // Ensure we have email in $params.
      if (empty($params['email']) && !empty($params['uid'])) {
        $user = $this->entityTypeManager->getStorage('user')->load($params['uid']);
        $params['email'] = $user->getEmail();
        unset($params['uid']);
      }
      foreach ($params as $param => $value) {
        $log->set($param, $value);
      }
      $log->setCreatedTime(time());
      $log->save();
      return $log;
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      return FALSE;
    }
  }

}
