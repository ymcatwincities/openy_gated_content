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
 * Class DownloadsIncrementController
 *
 * @package Drupal\openy_gc_shared_content_server\Controller
 */
class DownloadsIncrementController extends ControllerBase {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   *   EntityTypeManager service instance.
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
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Http request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function execute(Request $request) {

    $status = 'ok';

    $token = $request->get('token');
    $uuid = $request->get('uuid');
    $client_url = $request->get('client_url');

    $id = reset($this->entityTypeManager
      ->getStorage('shared_content_source')
      ->getQuery()
      ->condition('url', $client_url)
      ->execute());

    if (!empty($id)) {
      $source = SharedContentSource::load($id);
    } else {
      $status = 'error';
    }

    if ($source->getToken() !== $token) {
      $status = 'error';
    }

    $node = reset($this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties(['uuid' => $uuid]));

    if (!($node instanceof NodeInterface)) {
      $status = 'error';
    }

    if ($node->hasField('field_share_count')) {
      $current = $node->field_share_count->value;
      $current++;
      $node->field_share_count->value = $current;
      $node->save();
    } else {
      $status = 'error';
    }

    return JsonResponse::create(['status' => $status]);
  }

}
