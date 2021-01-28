<?php

namespace Drupal\openy_gc_auth_reclique_oauth2;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Session\SessionManager;
use Drupal\Core\Url;
use GuzzleHttp\Client as GuzzleHttpClient;
use Symfony\Component\HttpFoundation\Request;

/**
 * Reclique Oauth2 client wrapper service.
 *
 * @package Drupal\openy_gc_auth_reclique_oauth2
 */
class Client {

  const CSRF_TOKEN_VALUE = 'openy_gc_auth_reclique_oauth2';

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
   * Config for openy_gc_auth_reclique_oauth2 module.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $configRecliqueOauth2;

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
   * Session manager service.
   *
   * @var \Drupal\Core\Session\SessionManager
   */
  protected $sessionManager;

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
   * @param \Drupal\Core\Session\SessionManager $sessionManager
   *   SessionManager service.
   */
  public function __construct(
    LoggerChannelFactory $loggerFactory,
    ConfigFactoryInterface $configFactory,
    GuzzleHttpClient $client,
    CsrfTokenGenerator $csrfToken,
    SessionManager $sessionManager
  ) {
    $this->logger = $loggerFactory->get('openy_gc_auth_reclique_oauth2');
    $this->configFactory = $configFactory;
    $this->configRecliqueOauth2 = $configFactory->get('openy_gc_auth.provider.reclique_oauth2');
    $this->httpClient = $client;
    $this->csrfToken = $csrfToken;
    $this->sessionManager = $sessionManager;
  }

  /**
   * Get user data from Reclique.
   *
   * @param string $email
   *   User email.
   *
   * @return array|mixed
   *   User object array.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getUserData($email) {
    $provider_config = $this->configFactory->get('openy_gc_auth.provider.reclique_oauth2');

    $options = [
      'auth' => [
        $provider_config->get('auth_login'),
        $provider_config->get('auth_pass'),
      ],
      'query' => [
        'Email' => $email,
      ],
    ];

    try {
      $response = $this->httpClient->request('POST', $provider_config->get('verification_url'), $options);

      if ($response->getStatusCode() === '200') {
        $content = $response->getBody()->getContents();
        return json_decode($content, TRUE);
      }
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
    return [];
  }

  /**
   * Build authentication url.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return string
   *   Authentication Url.
   */
  public function buildAuthenticationUrl(Request $request) {
    if (!$request->hasPreviousSession()) {
      $this->sessionManager->regenerate();
    }

    $callbackUrl = Url::fromRoute('openy_gc_auth_reclique_oauth2.authenticate_callback', [], [
      'absolute' => TRUE,
    ])->toString(TRUE)->getGeneratedUrl();

    return Url::fromUri(
      $this->configRecliqueOauth2->get('authorization_server') . self::ENDPOINT_AUTHORIZE, [
        'absolute' => TRUE,
        'https' => TRUE,
        'query' => [
          'response_type' => 'code',
          'scope' => 'basic',
          'client_id' => $this->configRecliqueOauth2->get('client_id'),
          'redirect_uri' => $callbackUrl,
          'state' => $this->csrfToken->get(self::CSRF_TOKEN_VALUE),
        ],
      ])->toString(TRUE)->getGeneratedUrl();
  }

  /**
   * Validate csrf token.
   *
   * @param string $csrf_token
   *   Csrf token.
   *
   * @return bool
   *   Returns TRUE if scrf token is valid.
   */
  public function validateCsrfToken($csrf_token) {
    return $this->csrfToken->validate($csrf_token, self::CSRF_TOKEN_VALUE);
  }

  /**
   * Validate user subscription.
   *
   * @param object $userData
   *   User data.
   *
   * @return bool
   *   Returns TRUE if user has active subscription.
   *
   * @TODO fix
   */
  public function validateUserSubscription($userData) {
    return FALSE;
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
        $this->configRecliqueOauth2->get('authorization_server') . self::ENDPOINT_ACCESS_TOKEN,
        [
          'form_params' => [
            'grant_type' => 'authorization_code',
            'client_id' => $this->configRecliqueOauth2->get('client_id'),
            'client_secret' => $this->configRecliqueOauth2->get('client_secret'),
            'code' => $code,
            'redirect_uri' => Url::fromRoute('openy_gc_auth_reclique_oauth2.authenticate_redirect')
              ->toString(),
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
        $this->configRecliqueOauth2->get('authorization_server') . self::ENDPOINT_USER_DATA,
        [
          'headers' => [
            'Authorization' => "Bearer " . $access_token,
          ],
        ]);
      return json_decode((string) $response->getBody(), FALSE);
    }
    catch (\Exception $e) {
      return [
        'error' => 1,
        'message' => $e->getMessage(),
      ];
    }
  }

}
