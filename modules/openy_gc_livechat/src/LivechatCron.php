<?php

namespace Drupal\openy_gc_livechat;

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
   * Constructs a new instance.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * Main cron method.
   */
  public function cron() {
    $now = \Drupal::time()->getRequestTime();
    if ($this->shouldRun($now)) {
      $this->queueTasks();
      $this->state->set('openy_gc_livechat.last_cron', $now);
    }
  }

  /**
   * Helper method to check if cron tasks should run.
   *
   *  @param int $now
   *    Timestamp representing now.
   */
  public function shouldRun($now) {
    $scheduled = '01:00';
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
    // Delete chat messages older than 30 days.
    \Drupal::database()->delete('openy_gc_livechat__chat_history')
      ->condition('created', time() - 2592000, '<=')
      ->execute();
  }

}
