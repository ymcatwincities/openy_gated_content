<?php

namespace Drupal\openy_gc_shared_content\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Preview Controller for SharedContentFetchForm.
 */
class SharedContentProxyController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler) {
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('module_handler')
    );
  }

  /**
   * Callback for opening the modal form.
   */
  public function apiWrapper(Request $request, string $entity_type, string $entity_bundle, string $uuid = NULL) {
    if ($request->getMethod() != 'POST') {
      return new JsonResponse(['message' => 'Required POST method for request.'], 400);
    }
    // Validate by field_gc_share.
    $query = $request->query->all();
    if (!isset($query['filter']['field_gc_share']) && !(bool) $query['filter']['field_gc_share']) {
      return new JsonResponse(['message' => 'Required filter by field_gc_share field.'], 400);
    }

    // Validate token.
    $data = json_decode($request->getContent(), TRUE);
    if (!isset($data['url']) || !isset($data['token'])) {
      return new JsonResponse(['message' => 'Required data missed.'], 400);
    }

    if ($this->moduleHandler->moduleExists('openy_gc_shared_content_server')) {
      // On shared content server we should search by shared_content_source.
      $server_entity_type = 'shared_content_source';
    }
    else {
      // On client server we should search by shared_content_source_server.
      $server_entity_type = 'shared_content_source_server';
    }
    $entity_storage = $this->entityTypeManager->getStorage($server_entity_type);
    $entities = $entity_storage->loadByProperties([
      'url' => $data['url'],
      'token' => $data['token'],
    ]);

    if (empty($entities)) {
      return new JsonResponse(['message' => 'There no servers with specified url and token.'], 400);
    }

    $account = $this->currentUser();
    if (!$account->isAuthenticated()) {
      $user_storage = $this->entityTypeManager->getStorage('user');
      $users = $user_storage->loadByProperties([
        'mail' => 'shared.content@openy.com',
      ]);
      if (empty($users)) {
        return new JsonResponse(['message' => 'There no shared content user on selected server.'], 400);
      }

      $user = reset($users);
      user_login_finalize($user);
    }

    if ($uuid) {
      return $this->redirect('jsonapi.' . $entity_type . '--' . $entity_bundle . '.individual', ['entity' => $uuid], ['query' => $query]);
    }
    else {
      return $this->redirect('jsonapi.' . $entity_type . '--' . $entity_bundle . '.collection', [], ['query' => $query]);
    }
  }

}
