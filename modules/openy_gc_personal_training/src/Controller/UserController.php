<?php

namespace Drupal\openy_gc_personal_training\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provide actions to update current user name.
 */
class UserController extends ControllerBase {

  /**
   * Get current user name.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JsonResponse.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getName() {
    $user_storage = $this->entityTypeManager()->getStorage('user');
    $user = $user_storage->load($this->currentUser()->id());
    return new JsonResponse(['name' => $user->getAccountName()], 200);
  }

  /**
   * Update current user name.
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
  public function updateName(Request $request) {
    $params = json_decode($request->getContent(), TRUE);
    if (!isset($params['name'])) {
      return new JsonResponse(['message' => 'Argument name missed'], 400);
    }
    $user_storage = $this->entityTypeManager()->getStorage('user');
    $user = $user_storage->load($this->currentUser()->id());

    if ($user->getAccountName() == $params['name']) {
      return new JsonResponse(['message' => 'There no updates in user name'], 200);
    }

    try {
      $user->setUsername($params['name']);
      $user->save();
    }
    catch (\Exception $exception) {
      return new JsonResponse(['message' => 'User with that username already exists'], 400);
    }

    return new JsonResponse(['message' => 'Username successfully updated'], 200);
  }

}
