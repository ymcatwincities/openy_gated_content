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
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
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
   * @return AjaxResponse
   *   Return status
   */
  public function index(Request $request) {
    try {
//      $log = new LogEntity([], 'log_entity');
//      $log->set('email', 'test@mail.com');
//      $log->set('event_type', 'ViewEvent');
//      $log->set('entity_type', 'node');
//      $log->set('bundle', 'vblog');
//      $log->set('entity_id', '5');
//      $log->save();

      $data = $request->request->all();
      $log = new LogEntity($data, 'log_entity');
      $log->setCreatedTime(time());
      $log->save();

      return new AjaxResponse([
        'status' => 'ok',
      ]);
    } catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      return new AjaxResponse([
        'status' => $e->getMessage()
      ], AjaxResponse::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

}
