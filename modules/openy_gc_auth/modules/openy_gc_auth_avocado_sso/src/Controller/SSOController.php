<?php

namespace Drupal\openy_gc_auth_avocado_sso\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\openy_gc_auth\GCUserAuthorizer;
use Drupal\openy_gc_auth_avocado_sso\SSOClient;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class with controller endpoints, needed for avocado_sso plugin.
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
   * Avocado SSO OAuth2 client.
   *
   * @var \Drupal\openy_gc_auth_avocado_sso\SSOClient
   */
  protected $avocadoSSOClient;

  /**
   * SSOController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory instance.
   * @param \Drupal\openy_gc_auth\GCUserAuthorizer $gcUserAuthorizer
   *   The Gated User Authorizer.
   * @param \Drupal\openy_gc_auth_avocado_sso\SSOClient $avocadoSSOClient
   *   Avocado SSO OAuth2 Client.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    GCUserAuthorizer $gcUserAuthorizer,
    SSOClient $avocadoSSOClient
  ) {
    $this->configFactory = $configFactory;
    $this->configOpenyGatedContent = $configFactory->get('openy_gated_content.settings');
    $this->gcUserAuthorizer = $gcUserAuthorizer;
    $this->avocadoSSOClient = $avocadoSSOClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('openy_gc_auth.user_authorizer'),
      $container->get('openy_gc_auth.avocado_sso_client')
    );
  }

  /**
   * Redirect, login user and return authorization code.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request object.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   *   Redirect to the Avocado login page.
   */
  public function authenticationRedirect(Request $request): TrustedRedirectResponse {
    if (!empty($this->response)) {
      return $this->response;
    }

    $oAuth2AuthenticationUrl = $this->avocadoSSOClient->buildAuthenticationUrl($request);
    $this->response = new TrustedRedirectResponse($oAuth2AuthenticationUrl);
    $this->response->addCacheableDependency((new CacheableMetadata())->setCacheMaxAge(0));
    return $this->response;
  }

  /**
   * Perform authentication token validation, load user data & authorize the user.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request object.
   *
   * @return mixed
   *   Returns RedirectResponse or JsonResponse.
   */
  public function authorizationCallback(Request $request) {
    $user_data = $this->avocadoSSOClient
      ->setAuthenticationCode($request->query->get('code'))
      ->requestUserData();

    if (
      !$user_data
      || !isset($user_data->email)
      || empty($user_data->email)
    ) {
      return new RedirectResponse(
        URL::fromUserInput(
          $this->configOpenyGatedContent->get('virtual_y_login_url'),
          ['query' => ['error' => '1']]
        )->toString()
      );
    }

    $user_membership_data = $this->avocadoSSOClient->requestUserMembershipData($user_data->email);
    // Do not continue if barcode does not exist.
    if (
      !$user_membership_data
      || $user_membership_data->Barcode === null
    ) {
      return new RedirectResponse(
        URL::fromUserInput(
          $this->configOpenyGatedContent->get('virtual_y_login_url'),
          ['query' => ['error' => '1']]
        )->toString()
      );
    }

    $result = $this->avocadoSSOClient->createUserLoggedInEvent($user_membership_data->Barcode);

    // @TODO: Confirm condition for user subscription validation works.
    if ($this->avocadoSSOClient->validateUserSubscription($result)) {
      [$name, $email] = $this->avocadoSSOClient
        ->prepareUserNameAndEmail($user_data);

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
