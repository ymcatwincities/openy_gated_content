<?php

namespace Drupal\openy_gc_auth_reclique_sso;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use GuzzleHttp\Client as GuzzleHttpClient;
use Symfony\Component\HttpFoundation\Request;

/**
 * Reclique SSO OAuth2 client service.
 *
 * @package Drupal\openy_gc_auth_reclique_sso
 */
class SSOClient {

  const CSRF_TOKEN_VALUE = 'openy_gc_auth_reclique_sso';

  const ENDPOINT_LOGIN = '/login';

  const ENDPOINT_AUTHORIZE = '/api/oauth2/authorize';

  const ENDPOINT_ACCESS_TOKEN = '/api/oauth2/access_token';

  const ENDPOINT_USER_DATA = '/api/oauth2/endpoints/user';

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Config for openy_gc_auth_reclique_sso module.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $configRecliqueSSO;

  /**
   * Http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * CsrfToken service.
   *
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  protected $csrfToken;

  /**
   * Tempstore.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  private $tempStore;

  /**
   * RecliqueClientService constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerFactory
   *   LoggerChannelFactory instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   ConfigFactoryInterface instance.
   * @param \GuzzleHttp\Client $client
   *   Guzzle client.
   * @param \Drupal\Core\Access\CsrfTokenGenerator $csrfToken
   *   CsrfToken service.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   TempStore service.
   */
  public function __construct(
    LoggerChannelFactory $loggerFactory,
    ConfigFactoryInterface $configFactory,
    GuzzleHttpClient $client,
    CsrfTokenGenerator $csrfToken,
    PrivateTempStoreFactory $temp_store_factory
  ) {
    $this->logger = $loggerFactory->get('openy_gc_auth_reclique_sso');
    $this->configFactory = $configFactory;
    $this->configRecliqueSSO = $configFactory->get('openy_gc_auth.provider.reclique_sso');
    $this->httpClient = $client;
    $this->csrfToken = $csrfToken;
    $this->tempStore = $temp_store_factory->get('openy_gc_auth_reclique_sso');
  }

  /**
   * Build authentication url.
   *
   * This method also generates scrf token and starts a session
   * for anonymous user to be able to verify csrf token after
   * user return from authorization server.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return string
   *   Authentication Url.
   */
  public function buildAuthenticationUrl(Request $request) {
    $callbackUrl = Url::fromRoute('openy_gc_auth_reclique_sso.authenticate_callback', [], [
      'absolute' => TRUE,
    ])->toString(TRUE)->getGeneratedUrl();

    $authUrl = Url::fromUserInput(
      self::ENDPOINT_AUTHORIZE, [
        'absolute' => FALSE,
        'https' => TRUE,
        'query' => [
          'response_type' => 'code',
          'scope' => 'basic',
          'client_id' => $this->configRecliqueSSO->get('client_id'),
          'redirect_uri' => $callbackUrl,
          'state' => $this->csrfToken->get(self::CSRF_TOKEN_VALUE),
        ],
      ])->toString(TRUE)->getGeneratedUrl();

    $this->tempStore->set('save-csrf', TRUE);

    return Url::fromUri(
      $this->configRecliqueSSO->get('authorization_server') . self::ENDPOINT_LOGIN, [
        'https' => TRUE,
        'absolute' => TRUE,
        'query' => [
          'redirect' => $authUrl,
        ],
      ]
    )->toString(TRUE)->getGeneratedUrl();
  }

  /**
   * Validate csrf token.
   *
   * @param string $state
   *   CSRF State.
   *
   * @return bool
   *   Returns TRUE if scrf token is valid.
   */
  public function validateCsrfToken($state) {
    return $this->csrfToken->validate($state, self::CSRF_TOKEN_VALUE);
  }

  /**
   * Validate user subscription.
   *
   * @param object $userData
   *   User data.
   *
   * @return bool
   *   Returns TRUE if user has active subscription.
   */
  public function validateUserSubscription($userData) {
    return $userData->member->Status === 'Active';
  }

  /**
   * Prepare user name and email.
   *
   * @param object $userData
   *   User data.
   *
   * @return array
   *   Returns name and email.
   */
  public function prepareUserNameAndEmail($userData) {
    $name = "{$userData->member->FirstName} {$userData->member->LastName} {$userData->member->ID}";
    $email = "reclique_sso-{$userData->member->ID}@virtualy.openy.org";
    return [$name, $email];
  }

  /**
   * Request access token.
   *
   * @param string $code
   *   Authorization code.
   *
   * @return string
   *   Access token.
   */
  public function exchangeCodeForAccessToken($code) {
    try {
      $response = $this->httpClient->request(
        'POST',
        $this->configRecliqueSSO->get('authorization_server') . self::ENDPOINT_ACCESS_TOKEN,
        [
          'form_params' => [
            'grant_type' => 'authorization_code',
            'client_id' => $this->configRecliqueSSO->get('client_id'),
            'client_secret' => $this->configRecliqueSSO->get('client_secret'),
            'code' => urldecode($code),
            'redirect_uri' => Url::fromRoute('openy_gc_auth_reclique_sso.authenticate_callback', [], [
              'absolute' => TRUE,
            ])->toString(TRUE)->getGeneratedUrl(),
          ],
        ]);

      return json_decode((string) $response->getBody(), FALSE)->access_token;
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
  }

  /**
   * Request user data.
   *
   * @param string $access_token
   *   Access token.
   *
   * @return array|mixed
   *   User Data.
   */
  public function requestUserData($access_token) {
    try {
      $response = $this->httpClient->request(
        'GET',
        $this->configRecliqueSSO->get('authorization_server') . self::ENDPOINT_USER_DATA,
        [
          'headers' => [
            'Authorization' => "Bearer " . $access_token,
          ],
        ]);
      return json_decode((string) $response->getBody(), FALSE);
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
  }

}
