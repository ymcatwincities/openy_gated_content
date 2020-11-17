<?php

namespace Drupal\openy_gc_autologout\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns responses for openy_gc_autologout module routes.
 */
class VirtualYAutologoutController extends ControllerBase {

  /**
   * Logout current user on request.
   */
  public function logOut() {
    user_logout();
    return new JsonResponse([
      'logout' => 'true',
    ]);
  }

}
