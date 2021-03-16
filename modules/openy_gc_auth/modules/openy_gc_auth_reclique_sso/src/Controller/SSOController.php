<?php

namespace Drupal\openy_gc_auth_reclique_sso\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;
use Drupal\openy_gc_auth_reclique_sso\SSOClient;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\openy_gc_auth\GCUserAuthorizer;

/**
 * Class with controller endpoints, needed for reclique_sso plugin.
 */
class SSOController extends ControllerBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Config for openy_gated_content module.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $configOpenyGatedContent;

  /**
   * The Gated Content User Authorizer.
   *
   * @var \Drupal\openy_gc_auth\GCUserAuthorizer
   */
  protected $gcUserAuthorizer;

  /**
   * Reclique SSO OAuth2 client.
   *
   * @var \Drupal\openy_gc_auth_reclique_sso\SSOClient
   */
  protected $recliqueSSOClient;

  /**
   * SSOController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory instance.
   * @param \Drupal\openy_gc_auth\GCUserAuthorizer $gcUserAuthorizer
   *   The Gated User Authorizer.
   * @param \Drupal\openy_gc_auth_reclique_sso\SSOClient $recliqueSSOClient
   *   Reclique SSO OAuth2 Client.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    GCUserAuthorizer $gcUserAuthorizer,
    SSOClient $recliqueSSOClient
  ) {
    $this->configFactory = $configFactory;
    $this->configOpenyGatedContent = $configFactory->get('openy_gated_content.settings');
    $this->gcUserAuthorizer = $gcUserAuthorizer;
    $this->recliqueSSOClient = $recliqueSSOClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('openy_gc_auth.user_authorizer'),
      $container->get('openy_gc_auth.reclique_sso_client')
    );
  }

  /**
   * Redirect, login user and return authorization code.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request object.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   *   Redirect to the Reclique login page.
   */
  public function authenticateRedirect(Request $request): TrustedRedirectResponse {
    if (!empty($this->response)) {
      return $this->response;
    }

    $oAuth2AuthenticateUrl = $this->recliqueSSOClient->buildAuthenticationUrl($request);
    $this->response = new TrustedRedirectResponse($oAuth2AuthenticateUrl);
    $this->response->addCacheableDependency((new CacheableMetadata())->setCacheMaxAge(0));
    return $this->response;
  }

  /**
   * Receive authorization code, load user data and authorize user.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request object.
   *
   * @return mixed
   *   Returns RedirectResponse or JsonResponse.
   */
  public function authenticateCallback(Request $request) {
    // Check code that was generated by Open Y.
    if (!$this->recliqueSSOClient->validateCsrfToken($request->get('state'))) {
      return new RedirectResponse(
        URL::fromUserInput(
          $this->configOpenyGatedContent->get('virtual_y_login_url'),
          ['query' => ['error' => '1']]
        )->toString()
      );
    }

    $access_token = $this->recliqueSSOClient->exchangeCodeForAccessToken($request->get('code'));

    if (!$access_token) {
      return new RedirectResponse(
        URL::fromUserInput(
          $this->configOpenyGatedContent->get('virtual_y_login_url'),
          ['query' => ['error' => '1']]
        )->toString()
      );
    }

    $userData = $this->recliqueSSOClient->requestUserData($access_token);

    if (!$userData) {
      return new RedirectResponse(
        URL::fromUserInput(
          $this->configOpenyGatedContent->get('virtual_y_login_url'),
          ['query' => ['error' => '1']]
        )->toString()
      );
    }

    if ($this->recliqueSSOClient->validateUserSubscription($userData)) {
      [$name, $email] = $this->recliqueSSOClient
        ->prepareUserNameAndEmail($userData);

      // Authorize user (register, login, log, etc).
      $this->gcUserAuthorizer->authorizeUser($name, $email);

      return new RedirectResponse($this->configOpenyGatedContent->get('virtual_y_url'));
    }

    // Redirect back to Virual Y login page.
    return new RedirectResponse(
      URL::fromUserInput(
        $this->configOpenyGatedContent->get('virtual_y_login_url'),
        ['query' => ['error' => '1']]
      )->toString()
    );
  }

}