<?php

namespace Drupal\openy_gc_auth_custom\Plugin\rest;

use Drupal\rest\ModifiedResourceResponse;

/**
 * Error response for \Drupal\openy_gc_auth_custom\Plugin\rest\resource.
 */
trait ErrorResponseTrait {

  /**
   * Error Response.
   *
   * @param string $message
   *   The error message.
   * @param int $status
   *   The HTTP status code for error.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   */
  protected function errorResponse($message, $status) {
    return new ModifiedResourceResponse([
      'message' => $message,
      'status' => 'invalid',
    ], $status);
  }

}
