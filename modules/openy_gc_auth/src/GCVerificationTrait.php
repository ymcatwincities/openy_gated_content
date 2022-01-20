<?php

namespace Drupal\openy_gc_auth;

use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Trait Gated Content Verification Trait.
 *
 * @package Drupal\openy_gated_content
 */
trait GCVerificationTrait {

  /**
   * Defines if we need to verify the user in a current browser.
   *
   * @param \Drupal\user\Entity\User $user
   *   User object.
   *
   * @return bool
   *   Check result.
   */
  protected function isVerificationNeeded(User $user): bool {
    if (!$this->currentRequest->cookies->has('Drupal_visitor_gc_auth_authorized')) {
      return TRUE;
    }

    $verified_browsers = $this->userData->get('openy_gc_auth', $user->id(), 'verified_browsers');
    if (empty($verified_browsers)) {
      return TRUE;
    }

    $token = $this->currentRequest->cookies->get('Drupal_visitor_gc_auth_authorized');
    return !array_key_exists($token, $verified_browsers);
  }

  /**
   * Generates a new verification token and saves it to the user data.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param \Drupal\user\Entity\User $user
   *   User object.
   * @param int $current
   *   Current timestamp.
   *
   * @return string
   *   Token, stored to the user data.
   */
  protected function saveVerification(Request $request, User $user, int $current): string {
    $verified_browsers = [];
    if (!$request->cookies->has('Drupal_visitor_gc_auth_authorized')) {
      $verified_browsers = $this->userData->get('openy_gc_auth', $user->id(), 'verified_browsers');
    }
    $token = user_pass_rehash($user, $current);
    $verified_browsers[$token] = $current;
    $this->userData->set('openy_gc_auth', $user->id(), 'verified_browsers', $verified_browsers);
    return $token;
  }

}
