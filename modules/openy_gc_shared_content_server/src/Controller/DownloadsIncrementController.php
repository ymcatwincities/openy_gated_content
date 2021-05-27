<?php

namespace Drupal\openy_gc_shared_content_server\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\openy_gc_shared_content_server\Entity\SharedContentSource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DownloadsIncrement Controller.
 *
 * @package Drupal\openy_gc_shared_content_server\Controller
 */
class DownloadsIncrementController extends ControllerBase {

  /**
   * EntityTypeManager service instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypemanager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Callback for increment execution.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Http request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JsonResponse class instance.
   */
  public function execute(Request $request) {

    $status = 'ok';

    $token = $request->get('token');
    $uuid = $request->get('uuid');
    $client_url = $request->get('client_url');
    $ids = $this->entityTypeManager
      ->getStorage('shared_content_source')
      ->getQuery()
      ->condition('url', $client_url)
      ->execute();
    $id = reset($ids);

    if (!empty($id)) {
      $source = SharedContentSource::load($id);
    }
    else {
      $status = 'error';
    }

    if ($source->getToken() !== $token) {
      $status = 'error';
    }
    $nodes = $this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties(['uuid' => $uuid]);
    $node = reset($nodes);

    if (!($node instanceof NodeInterface)) {
      $status = 'error';
    }

    if ($node->hasField('field_share_count')) {
      $current = $node->field_share_count->value;
      $current++;
      $node->field_share_count->value = $current;
      $node->save();
    }
    else {
      $status = 'error';
    }

    return JsonResponse::create(['status' => $status]);
  }

}
