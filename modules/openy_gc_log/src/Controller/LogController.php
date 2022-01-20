<?php

namespace Drupal\openy_gc_log\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\openy_gc_log\Entity\LogEntity;
use Drupal\openy_gc_log\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Log Controller class.
 */
class LogController extends ControllerBase {

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The Gated Content Logger.
   *
   * @var \Drupal\openy_gc_log\Logger
   */
  protected $gcLogger;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   Logger Factory.
   * @param \Drupal\openy_gc_log\Logger $gcLogger
   *   The Gated Content Logger.
   */
  public function __construct(
    LoggerChannelFactoryInterface $loggerFactory,
    Logger $gcLogger = NULL
  ) {
    $this->logger = $loggerFactory->get('openy_gc_log');
    $this->gcLogger = $gcLogger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory'),
      $container->has('openy_gc_log.logger') ? $container->get('openy_gc_log.logger') : NULL
    );
  }

  /**
   * Index.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return status
   */
  public function index(Request $request) {
    $content = $request->getContent();
    $params = json_decode($content, TRUE);
    // We should be sure we are logging activity of the current user.
    $params['uid'] = $this->currentUser()->id();
    $params['email'] = $this->currentUser()->getEmail();
    $status = $this->gcLogger->addLog($params);
    if ($status instanceof LogEntity) {
      return new AjaxResponse([
        'status' => 'ok',
      ]);
    }

    return new AjaxResponse([
      'status' => 'error',
    ], AjaxResponse::HTTP_INTERNAL_SERVER_ERROR);
  }

  /**
   * Track activity.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return status
   */
  public function trackActivity(Request $request) {
    $status = $this->gcLogger->trackActivity($this->currentUser()->id());
    if ($status instanceof LogEntity) {
      return new AjaxResponse([
        'status' => 'ok',
      ]);
    }

    return new AjaxResponse([
      'status' => 'error',
    ], AjaxResponse::HTTP_INTERNAL_SERVER_ERROR);
  }

}
