<?php

namespace Drupal\openy_gc_personal_training\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provide actions to save and load customer peer id.
 */
class PeerController extends ControllerBase {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new PeerController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
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
   * Publish customer peer id.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JsonResponse.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @TODO set route permissions
   */
  public function publishCustomerPeer(Request $request) {
    $personalTrainingId = $request->get('trainingId');
    $peerId = $request->get('peerId');

    if (empty($personalTrainingId) || empty($peerId)) {
      return new JsonResponse('Not enough data', 500);
    }

    $trainingEntity = $this->entityTypeManager
      ->getStorage('personal_training')
      ->load($personalTrainingId);

    if (!$trainingEntity) {
      return new JsonResponse('Entity not found', 500);
    }

    $trainingEntity->setCustomerPeerId($peerId);
    $trainingEntity->save();

    return new JsonResponse();
  }

  /**
   * Load customer peer id.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JsonResponse.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @TODO set route permissions
   */
  public function loadCustomerPeer(Request $request) {
    $personalTrainingId = $request->get('trainingId');
    if (empty($personalTrainingId)) {
      return new JsonResponse('Not enough data', 500);
    }

    $trainingEntity = $this->entityTypeManager
      ->getStorage('personal_training')
      ->load($personalTrainingId);

    if (!$trainingEntity) {
      return new JsonResponse('Entity not found', 500);
    }

    if ($peerId = $trainingEntity->getCustomerPeerId()) {
      return new JsonResponse($peerId);
    }

    return new JsonResponse(NULL, 204);
  }

}
