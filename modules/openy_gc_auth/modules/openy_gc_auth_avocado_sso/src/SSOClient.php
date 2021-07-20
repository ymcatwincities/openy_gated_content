<?php

namespace Drupal\openy_gc_auth_avocado_sso;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Url;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Avocado SSO OAuth2 client service.
 *
 * @package Drupal\openy_gc_auth_avocado_sso
 */
class SSOClient {

  const ENDPOINT_AUTHORIZE = '/services/oauth2/authorize';

  const ENDPOINT_ACCESS_TOKEN = '/services/oauth2/token';

  const ENDPOINT_USER_ACCOUNT_INFO = '/services/oauth2/userinfo';

  const ENDPOINT_USER_MEMBERSHIP_INFO = '/services/apexrest/{YMCA_EXTENSION}/PrivateAvoAPI/v1/communityLogin';

  const ENDPOINT_COMMUNITY_EVENT_LOG = '/services/apexrest/{YMCA_EXTENSION}/PrivateAvoAPI/v1/communityEventLog';

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
   * Access token taken from external Avocado service.
   *
   * @var string
   */
  protected $accessToken;

  /**
   * Authentication code retrieved from request made in buildAuthenticationUrl().
   *
   * @var string
   *
   * @see \Drupal\openy_gc_auth_avocado_sso\SSOClient::buildAuthenticationUrl()
   */
  protected $authenticationCode;

  /**
   * Avocado SSOClient constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerFactory
   *   LoggerChannelFactory instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   ConfigFactoryInterface instance.
   * @param \GuzzleHttp\Client $client
   *   Guzzle client.
   */
  public function __construct(
    LoggerChannelFactory $loggerFactory,
    ConfigFactoryInterface $configFactory,
    GuzzleHttpClient $client
  ) {
    $this->logger = $loggerFactory->get('openy_gc_auth_avocado_sso');
    $this->configFactory = $configFactory;
    $this->configAvocadoSSO = $configFactory->get('openy_gc_auth.provider.avocado_sso');
    $this->httpClient = $client;
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
      'https' => TRUE,
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
   * Retrieve access token from Avocado.
   *
   * @return string
   *   Authentication token.
   *
   * @see \Drupal\openy_gc_auth_avocado_sso\SSOClient::buildAuthenticationUrl()
   */
  public function retrieveAccessToken() {
    $code = $this->getAuthenticationCode();

    if (empty($code)) {
      $this->logger->error('Authentication code is missing.');

      return '';
    }

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
              'https' => TRUE,
            ])->toString(TRUE)->getGeneratedUrl(),
          ],
        ]);

      return json_decode((string) $response->getBody(), FALSE)->access_token;
    }
    catch (GuzzleException $e) {
      $this->logger->error($e->getMessage());

      return '';
    }
  }

  /**
   * Request user data from Avocado.
   *
   * @return array|mixed
   *   User Data.
   */
  public function requestUserData() {
    try {
      $response = $this->httpClient->request(
        'GET',
        $this->configAvocadoSSO->get('authentication_server') . self::ENDPOINT_USER_ACCOUNT_INFO,
        [
          'query' => [
            'oauth_token' => $this->getAccessToken()
          ],
        ]);
      return json_decode((string) $response->getBody(), FALSE);
    }
    catch (GuzzleException $e) {
      $this->logger->error($e->getMessage());

      return [];
    }
  }

  /**
   * @param string $user_email
   * @return mixed
   */
  public function requestUserMembershipData(string $user_email) {
    try {
      $response = $this->httpClient->request(
        'GET',
        $this->configAvocadoSSO->get('authentication_server') . str_replace('{YMCA_EXTENSION}', $this->configAvocadoSSO->get('ymca_extension'),self::ENDPOINT_USER_MEMBERSHIP_INFO),
        [
          'headers' => [
            'Authorization' => "Bearer " . $this->getAccessToken(),
          ],
          'query' => [
            'username' => $user_email,
          ],
        ]);
      $body = json_decode((string) $response->getBody(), FALSE);
      if ($body->message !== "Success") {
        $this->logger->error('The request for user membership data has not been successful.');
        return [];
      }
      return $body->data[0];
    }
    catch (GuzzleException $e) {
      $this->logger->error($e->getMessage());

      return [];
    }
  }

  /**
   * @param string $member_barcode
   * @return false|mixed
   */
  public function createUserLoggedInEvent(string $member_barcode) {
    try {
      $request_uri = $this->configAvocadoSSO->get('authentication_server') .
        str_replace('{YMCA_EXTENSION}', $this->configAvocadoSSO->get('ymca_extension'),self::ENDPOINT_COMMUNITY_EVENT_LOG);

      $response = $this->httpClient->request(
        'POST',
        $request_uri,
        [
          'headers' => [
            'Authorization' => "Bearer " . $this->getAccessToken(),
          ],
          'form_params' => [
            'memberBarcode' => $member_barcode,
            'locationCode' => $this->configAvocadoSSO->get('location_code'),
          ],
        ]);

      return json_decode((string) $response->getBody(), FALSE);
    }
    catch (GuzzleException $e) {
      $this->logger->error($e->getMessage());

      return FALSE;
    }
  }

  /**
   * Getter for Access token property.
   *
   * @return string
   *   Authentication token.
   */
  public function getAccessToken():string {
    if (empty($this->accessToken)) {
      $this->setAccessToken($this->retrieveAccessToken());
    }

    return $this->accessToken;
  }

  /**
   * Setter for Access token property.
   *
   * @param string $access_token
   *   Access token
   * @return $this
   */
  public function setAccessToken(string $access_token) {
    $this->accessToken = $access_token;

    return $this;
  }

  /**
   * Getter for Authentication code property.
   *
   * @return string
   *   Authentication code.
   */
  public function getAuthenticationCode():string {
    return $this->authenticationCode;
  }

  /**
   * Setter for Authentication code property.
   *
   * @param string $authentication_code
   *   Authentication code retrieved from request made in buildAuthenticationUrl().
   *
   * @return $this
   *   Current object.
   *
   * @see \Drupal\openy_gc_auth_avocado_sso\SSOClient::buildAuthenticationUrl()
   */
  public function setAuthenticationCode(string $authentication_code) {
    $this->authenticationCode = $authentication_code;

    return $this;
  }

}
