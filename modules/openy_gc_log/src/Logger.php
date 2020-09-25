<?php

namespace Drupal\openy_gc_log;

use Drupal\Core\Logger\LoggerChannel;
use Drupal\openy_gc_log\Entity\LogEntity;
use Drupal\user\Entity\User;

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
   * LogArchiver constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannel $logger
   *   LoggerChannel.
   */
  public function __construct(LoggerChannel $logger) {
    $this->logger = $logger;
  }

  /**
   * Create log_entity.
   *
   * @param array $params
   *   Array with parameters.
   *
   * @return boolean|Entity\LogEntity
   *   Return false or log_entity.
   */
  public function addLog($params) {
    try {
      $log = new LogEntity([], 'log_entity');
      // Ensure we have email in $params.
      if (empty($params['email']) && !empty($params['uid'])) {
        $user = User::load($params['uid']);
        $params['email'] = $user->getEmail();
        unset($params['uid']);
      }
      else {
        return FALSE;
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
