<?php

namespace Drupal\openy_gc_auth_custom\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Flood\FloodInterface;
use Drupal\openy_gc_auth\GCUserAuthorizer;
use Drupal\user\UserStorageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Controller routines for user routes.
 */
class UserController extends ControllerBase {

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The flood service.
   *
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $flood;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $datetime;

  /**
   * The Gated Content User Authorizer.
   *
   * @var \Drupal\openy_gc_auth\GCUserAuthorizer
   */
  protected $gcUserAuthorizer;

  /**
   * Constructs a UserController object.
   *
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user storage.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Flood\FloodInterface $flood
   *   The flood service.
   * @param \Drupal\Component\Datetime\TimeInterface $datetime
   *   The time service.
   * @param \Drupal\openy_gc_auth\GCUserAuthorizer $gcUserAuthorizer
   *   The Gated User Authorizer.
   */
  public function __construct(UserStorageInterface $user_storage, LoggerInterface $logger, FloodInterface $flood, TimeInterface $datetime, GCUserAuthorizer $gcUserAuthorizer) {
    $this->userStorage = $user_storage;
    $this->logger = $logger;
    $this->flood = $flood;
    $this->datetime = $datetime;
    $this->gcUserAuthorizer = $gcUserAuthorizer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('logger.factory')->get('user'),
      $container->get('flood'),
      $container->get('datetime.time'),
      $container->get('openy_gc_auth.user_authorizer')
    );
  }

  /**
   * Redirects to the user password reset form.
   *
   * In order to never disclose a reset link via a referrer header this
   * controller must always return a redirect response.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param int $uid
   *   User ID of the user requesting reset.
   * @param int $timestamp
   *   The current timestamp.
   * @param string $hash
   *   Login link hash.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect response.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function verifyAccount(Request $request, $uid, $timestamp, $hash) {
    if (!$uid || !$hash || !$timestamp) {
      throw new AccessDeniedHttpException();
    }
    $vy_settings = $this->config('openy_gated_content.settings');
    $account = $this->currentUser();
    // When processing the one-time login link, we have to make sure that a user
    // isn't already logged in.
    if ($account->isAuthenticated()) {
      // The current user is already logged in.
      return new RedirectResponse($vy_settings->get('virtual_y_url'), 302);
    }

    $user = $this->userStorage->load($uid);
    if ($user === NULL) {
      // Verify that the user exists.
      throw new AccessDeniedHttpException();
    }

    $current = $this->datetime->getRequestTime();
    $timeout = $this->config('openy_gc_auth.provider.custom')->get('email_verification_link_life_time');
    if ($user->getLastLoginTime() && $current - $timestamp > $timeout) {
      $this->messenger()->addError($this->t('You have tried to use a one-time login link that has expired. Please request a new one using the form below.'));
      return new RedirectResponse($vy_settings->get('virtual_y_login_url'), 302);
    }
    elseif ($user->isAuthenticated() && ($timestamp >= $user->getLastLoginTime()) && ($timestamp <= $current) && hash_equals($hash, user_pass_rehash($user, $timestamp))) {
      $this->gcUserAuthorizer->authorizeUser($user->getAccountName(), $user->getEmail());
      // Clear any flood events for this IP.
      $this->flood->clear('openy_gc_auth_custom.login');
      return new RedirectResponse($vy_settings->get('virtual_y_url'), 302);
    }

    $this->messenger()->addError($this->t('You have tried to use a one-time login link that has either been used or is no longer valid. Please request a new one using the form below.'));
    return new RedirectResponse($vy_settings->get('virtual_y_login_url'), 302);
  }

}
