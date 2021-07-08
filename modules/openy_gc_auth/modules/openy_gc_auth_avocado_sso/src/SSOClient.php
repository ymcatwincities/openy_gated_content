<?php

namespace Drupal\openy_gc_auth_avocado_sso;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use GuzzleHttp\Client as GuzzleHttpClient;
use Symfony\Component\HttpFoundation\Request;

/**
 * Avocado SSO OAuth2 client service.
 *
 * @package Drupal\openy_gc_auth_avocado_sso
 */
class SSOClient {

  const CSRF_TOKEN_VALUE = 'openy_gc_auth_avocado_sso';

  const ENDPOINT_AUTHORIZE = '/services/oauth2/authorize';

  const ENDPOINT_ACCESS_TOKEN = '/services/oauth2/token';

  const ENDPOINT_USER_ACCOUNT_INFO = '/services/oauth2/userinfo';

  const ENDPOINT_USER_MEMBERSHIP_INFO = '/services/apexrest/{YMCA_EXTENSION}/PrivateAvoApi/communityLogin';

  const ENDPOINT_COMMUNITY_EVENT_LOG = '/services/apexrest/{YMCA_EXTENSION}/PrivateAvoApi/communityEventLog';

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
   * Config for openy_gc_auth_avocado_sso module.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $configAvocadoSSO;

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
   * Avocado SSOClient constructor.
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
    $this->logger = $loggerFactory->get('openy_gc_auth_avocado_sso');
    $this->configFactory = $configFactory;
    $this->configAvocadoSSO = $configFactory->get('openy_gc_auth.provider.avocado_sso');
    $this->httpClient = $client;
    $this->csrfToken = $csrfToken;
    $this->tempStore = $temp_store_factory->get('openy_gc_auth_avocado_sso');
  }

  /**
   * Build Avocado SSO authentication url.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return string
   *   Avocado SSO Authentication Url.
   */
  public function buildAuthenticationUrl(Request $request) {
    $callbackUrl = Url::fromRoute('openy_gc_auth_avocado_sso.authorization_callback', [], [
      'absolute' => TRUE,
    ])->toString(TRUE)->getGeneratedUrl();

    return Url::fromUri(
      $this->configAvocadoSSO->get('authentication_server') . self::ENDPOINT_AUTHORIZE, [
        'https' => TRUE,
        'absolute' => TRUE,
        'query' => [
          'client_id' => $this->configAvocadoSSO->get('client_id'),
          'redirect_uri' => $callbackUrl,
          'response_type' => 'code',
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
   * Check if user subscription is active.
   *
   * This can be achieved by checking the result of the community event
   * creation, done in the createUserLoggedInEvent method.
   *
   * @param object $community_event_result
   *   User membership data.
   *   Possible values:
   *     - 'Success': This occurs if the user does not have any account
   *       exceptions blocking access, the user has access levels and the event
   *       log was created successfully;
   *     - 'Access Denied': This occurs if the user has an account exception
   *       that blocks access, does not have access levels or there was an issue
   *       when creating the event log.
   *
   * @return bool
   *   Returns TRUE if the community_event_result is 'Success'.
   *
   * @see \Drupal\openy_gc_auth_avocado_sso\SSOClient::createUserLoggedInEvent()
   */
  public function validateUserSubscription($community_event_result) {
    return $community_event_result === 'Success';
  }

  /**
   * Prepare user name and email.
   *
   * @param object $user_data
   *   User data.
   *
   * @return array
   *   Returns name and email.
   */
  public function prepareUserNameAndEmail($user_data) {
    $name = "{$user_data->member->FirstName} {$user_data->member->LastName} {$user_data->member->ID}";
    $email = "avocado_sso-{$user_data->member->ID}@virtualy.openy.org";
    return [$name, $email];
  }

  /**
   * Retrieve authentication token.
   *
   * @param string $code
   *   Authentication code retrieved from request made in buildAuthenticationUrl().
   *
   * @return string
   *   Authentication token.
   *
   * @see \Drupal\openy_gc_auth_avocado_sso\SSOClient::buildAuthenticationUrl()
   */
  public function retrieveAuthenticationToken($code) {
    try {
      $response = $this->httpClient->request(
        'POST',
        $this->configAvocadoSSO->get('authentication_server') . self::ENDPOINT_ACCESS_TOKEN,
        [
          'form_params' => [
            'code' => urldecode($code),
            'grant_type' => 'authorization_code',
            'client_id' => $this->configAvocadoSSO->get('client_id'),
            'client_secret' => $this->configAvocadoSSO->get('client_secret'),
            'redirect_uri' => Url::fromRoute('openy_gc_auth_avocado_sso.authorization_callback', [], [
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
   * Request user data from Avocado.
   *
   * @param string $authentication_token
   *   Authentication token retrieved from retrieveAuthenticationToken.
   *
   * @return array|mixed
   *   User Data.
   *
   * @see \Drupal\openy_gc_auth_avocado_sso\SSOClient::retrieveAuthenticationToken()
   */
  public function requestUserData(string $authentication_token) {
    try {
      $response = $this->httpClient->request(
        'GET',
        $this->configAvocadoSSO->get('authentication_server') . self::ENDPOINT_USER_ACCOUNT_INFO,
        [
          'query' => [
            'oauth_token' => $authentication_token
          ],
        ]);
      return json_decode((string) $response->getBody(), FALSE);
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
  }

  /**
   * @param string $authentication_token
   * @param string $user_email
   * @return mixed
   */
  public function requestUserMembershipData(string $authentication_token, string $user_email) {
    try {
      $response = $this->httpClient->request(
        'GET',
        $this->configAvocadoSSO->get('authentication_server') . str_replace('{YMCA_EXTENSION}', $this->configAvocadoSSO->get('ymca_extension'),self::ENDPOINT_USER_MEMBERSHIP_INFO),
        [
          'headers' => [
            'Authorization' => "Bearer " . $authentication_token,
          ],
          'query' => [
            'username' => $user_email,
          ],
        ]);
      return json_decode((string) $response->getBody(), FALSE);
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
  }

  public function createUserLoggedInEvent(string $authentication_token, string $member_barcode) {
    try {
      $request_uri = $this->configAvocadoSSO->get('authentication_server') .
        str_replace('{YMCA_EXTENSION}', $this->configAvocadoSSO->get('ymca_extension'),self::ENDPOINT_COMMUNITY_EVENT_LOG);

      $response = $this->httpClient->request(
        'POST',
        $request_uri,
        [
          'headers' => [
            'Authorization' => "Bearer " . $authentication_token,
          ],
          'form_params' => [
            'memberBarcode' => $member_barcode,
            'locationCode' => $this->configAvocadoSSO->get('location_code'),
          ],
        ]);

      return json_decode((string) $response->getBody(), FALSE);
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
  }

}
