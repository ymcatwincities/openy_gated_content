<?php

namespace Drupal\openy_gc_auth_custom\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Represents resource for Virtual YMCA authentication provider.
 *
 * @RestResource(
 *   id = "openy_gc_auth_custom",
 *   label = @Translation("Custom authentication provider for Virtual YMCA"),
 *   uri_paths = {
 *     "create" = "/openy-gc-auth/provider/custom/login"
 *   }
 * )
 *
 * @deprecated in openy_gated_content:0.16.
 */
class GatedContentCustomAuthentication extends ResourceBase {

  /**
   * {@inheritdoc}
   */
  public function post(Request $request) {

  }

}
