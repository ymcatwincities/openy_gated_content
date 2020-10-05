<?php

namespace Drupal\openy_gc_auth_custom\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Represents resource for Virtual YMCA authentication provider.
 *
 * @RestResource(
 *   id = "openy_gc_auth_custom_confirm",
 *   label = @Translation("Email confirmation for Custom authentication provider"),
 *   uri_paths = {
 *     "create" = "/openy-gc-auth/provider/custom/login-by-link"
 *   }
 * )
 *
 * @deprecated in openy_gated_content:0.16.
 */
class GatedContentCustomAuthConfirm extends ResourceBase {

  /**
   * {@inheritdoc}
   */
  public function post(Request $request) {

  }

}
