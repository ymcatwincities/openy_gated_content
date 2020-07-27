<?php

namespace Drupal\openy_gc_log\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\openy_gc_log\Entity\LogEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

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
   * Constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   Logger Factory.
   */
  public function __construct(LoggerChannelFactoryInterface $loggerFactory) {
    $this->logger = $loggerFactory->get('openy_gc_log');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory')
    );
  }

  /**
   * Index.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Return status
   */
  public function index(Request $request) {
    try {
      $content = $request->getContent();
      if ($content) {
        $params = json_decode($content, TRUE);

        $log = new LogEntity([], 'log_entity');
        foreach ($params as $param => $value) {
          $log->set($param, $value);
        }
        $log->setCreatedTime(time());
        $log->save();
      }

      return new AjaxResponse([
        'status' => 'ok',
      ]);
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      return new AjaxResponse([
        'status' => 'error',
      ], AjaxResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

}
