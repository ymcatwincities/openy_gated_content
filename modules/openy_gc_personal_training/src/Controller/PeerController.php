<?php

namespace Drupal\openy_gc_personal_training\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxy;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
   * Current User.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Constructs a new PeerController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   * @param \Drupal\Core\Session\AccountProxy $currentUser
   *   Current User.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    AccountProxy $currentUser
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user')
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
      return new JsonResponse('Entity not found', 404);
    }

    if ($trainingEntity->getCustomerId() !== $this->currentUser->id()) {
      return new JsonResponse('Access denied', 403);
    }

    try {
      $trainingEntity
        ->setCustomerPeerId($peerId)
        ->save();
      return new JsonResponse();
    }
    catch (\Exception $e) {
      return new JsonResponse('Failed to save peer id', 500);
    }
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
      return new JsonResponse('Entity not found', 404);
    }

    if ($trainingEntity->getInstructorId() !== $this->currentUser->id()) {
      return new JsonResponse('Access denied', 403);
    }

    if ($peerId = $trainingEntity->getCustomerPeerId()) {
      return new JsonResponse($peerId);
    }

    return new JsonResponse(NULL, 204);
  }

}
