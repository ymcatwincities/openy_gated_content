<?php

namespace Drupal\openy_gc_livechat;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\State\StateInterface;

/**
 * Provide cron tasks for GC Livechat module.
 */
class LivechatCron {

  /**
   * The state store.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The database connection used to store flood event information.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection which will be used to store the flood event
   */
  public function __construct(StateInterface $state, TimeInterface $time, ConfigFactoryInterface $configFactory, Connection $connection) {
    $this->state = $state;
    $this->time = $time;
    $this->configFactory = $configFactory;
    $this->connection = $connection;
  }

  /**
   * Main cron method.
   */
  public function cron() {
    $now =  $this->time->getRequestTime();
    if ($this->shouldRun($now)) {
      $this->queueTasks();
      $this->state->set('openy_gc_livechat.last_cron', $now);
    }
  }

  /**
   * Helper method to check if cron tasks should run.
   *
   * @param int $now
   *   Timestamp representing now.
   */
  public function shouldRun($now) {
    $scheduled = $this->configFactory->get('openy_gc_livechat.settings')->get('scheduled');
    if (empty($scheduled)) {
      $scheduled = '01:00';
    }
    $timezone = new \DateTimeZone(date_default_timezone_get());

    $timestamp_last = $this->state->get('openy_gc_livechat.last_cron') ?? 0;
    $last = DrupalDateTime::createFromFormat('U', $timestamp_last)
      ->setTimezone($timezone);
    $next = clone $last;

    $next->setTime(...explode(':', $scheduled));
    // If the cron ran on the same calendar day it should have, add one day.
    if ($next->getTimestamp() <= $last->getTimestamp()) {
      $next->modify('+1 day');
    }

    return $next->getTimestamp() <= $now;
  }

  /**
   * Tasks running on cron.
   */
  public function queueTasks() {
    // Delete chat messages older than interval days.
    $interval = $this->configFactory->get('openy_gc_livechat.settings')->get('interval');
    if (empty($interval)) {
      $interval = 30;
    }
    $this->connection->delete('openy_gc_livechat__chat_history')
      ->condition('created', time() - $interval * 24 * 60 * 60, '<=')
      ->execute();
  }

}
