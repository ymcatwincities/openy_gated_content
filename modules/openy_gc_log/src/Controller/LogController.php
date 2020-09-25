<?php

namespace Drupal\openy_gc_log\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\openy_gc_log\Entity\LogEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\openy_gc_log\Logger;

/**
 * Class LogController.
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
    Logger $gcLogger
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
      $container->get('openy_gc_log.logger')
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
    $status = $this->gcLogger->addLog($params);
    if ($status instanceof LogEntity) {
      return new AjaxResponse([
        'status' => 'ok',
      ]);
    }
    else {
      return new AjaxResponse([
        'status' => 'error',
      ], AjaxResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

}
