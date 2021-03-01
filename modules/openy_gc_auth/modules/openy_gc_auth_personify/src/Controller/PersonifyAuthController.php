<?php

namespace Drupal\openy_gc_auth_personify\Controller;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\personify\PersonifyClient;
use Drupal\personify\PersonifySSO;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\openy_gc_auth\GCUserAuthorizer;
use Drupal\openy_gc_auth_personify\LogoutClient;

/**
 * Personify controller to handle Personify SSO authentication.
 */
class PersonifyAuthController extends ControllerBase {

  /**
   * Personify SSO service.
   *
   * @var \Drupal\personify\PersonifySSO
   */
  protected $personifySSO;

  /**
   * Personify Client service.
   *
   * @var \Drupal\personify\PersonifyClient
   */
  protected $personifyClient;

  /**
   * Logger interface.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The Gated Content User Authorizer.
   *
   * @var \Drupal\openy_gc_auth\GCUserAuthorizer
   */
  protected $gcUserAuthorizer;

  /**
   * Event Dispatcher.
   *
   * @var \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  protected $eventDispatcher;

  /**
   * Provider client.
   *
   * @var \Drupal\openy_gc_auth_personify\LogoutClient
   */
  protected $logoutClient;

  /**
   * PersonifyAuthController constructor.
   *
   * @param \Drupal\personify\PersonifySSO $personifySSO
   *   Personify SSO service.
   * @param \Drupal\personify\PersonifyClient $personifyClient
   *   Personify Client service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerChannelFactory
   *   Logger factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\openy_gc_auth\GCUserAuthorizer $gcUserAuthorizer
   *   The Gated User Authorizer.
   * @param \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher $eventDispatcher
   *   Event Dispatcher.
   * @param \Drupal\openy_gc_auth_personify\LogoutClient $logoutClient
   *   Logout client.
   */
  public function __construct(
    PersonifySSO $personifySSO,
    PersonifyClient $personifyClient,
    ConfigFactoryInterface $configFactory,
    LoggerChannelFactory $loggerChannelFactory,
    MessengerInterface $messenger,
    GCUserAuthorizer $gcUserAuthorizer,
    ContainerAwareEventDispatcher $eventDispatcher,
    LogoutClient $logoutClient
  ) {
    $this->personifySSO = $personifySSO;
    $this->personifyClient = $personifyClient;
    $this->configFactory = $configFactory;
    $this->logger = $loggerChannelFactory->get('openy_gc_auth_personify');
    $this->messenger = $messenger;
    $this->gcUserAuthorizer = $gcUserAuthorizer;
    $this->eventDispatcher = $eventDispatcher;
    $this->logoutClient = $logoutClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('personify.sso_client'),
      $container->get('personify.client'),
      $container->get('config.factory'),
      $container->get('logger.factory'),
      $container->get('messenger'),
      $container->get('openy_gc_auth.user_authorizer'),
      $container->get('event_dispatcher'),
      $container->get('openy_gc_auth_personify.logout_client')
    );
  }

  /**
   * Set cookies to authenticate user based on response from Personify.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function auth(Request $request) {
    $errorMessage = 'Login failed because of mismatched Personify response.';

    $query = $request->query->all();
    if (isset($query['ct']) && !empty($query['ct'])) {
      $errorMessage = 'An attempt to login with wrong personify token was detected.';

      $decrypted_token = $this->personifySSO->decryptCustomerToken($query['ct']);
      if ($token = $this->personifySSO->validateCustomerToken($decrypted_token)) {
        if ($this->userHasActiveMembership($token)) {
          $userInfo = $this->personifySSO->getCustomerInfo($token);
          $errorMessage = NULL;
          user_cookie_save([
            'personify_authorized' => $token,
            'personify_time' => REQUEST_TIME,
          ]);
        }
        else {
          $isUserSuccessfullyLogout = $this->logoutClient->logout($token);
          if ($isUserSuccessfullyLogout) {
            user_cookie_delete('personify_authorized');
            user_cookie_delete('personify_time');
          }

          $path = URL::fromUserInput(
            $this->configFactory->get('openy_gated_content.settings')->get('virtual_y_login_url'),
            ['query' => ['personify-error' => '1']]
          )->toString();
          return new RedirectResponse($path);
        }
      }
    }

    // Failed auth attempt.
    if ($errorMessage) {
      $this->logger->warning($errorMessage);
    }

    $redirect_url = $this->configFactory->get('openy_gated_content.settings')->get('virtual_y_url');
    if (isset($query['dest'])) {
      $redirect_url = urldecode($query['dest']);
    }

    if (!empty($userInfo) && !empty($userInfo['UserExists']) && empty($userInfo['DisableAccountFlag'])) {
      $name = !empty($userInfo['UserName']) ? $userInfo['UserName'] : '';
      $email = !empty($userInfo['Email']) ? $userInfo['Email'] : '';

      // Authorize user (register, login, log, etc).
      $this->gcUserAuthorizer->authorizeUser($name, $email);
    }

    $redirect = new TrustedRedirectResponse($redirect_url);
    $redirect->send();

    exit();
  }

  /**
   * Check whether user is already logged in to Personify.
   *
   * @return mixed
   *   Returns RedirectResponse or JsonResponse.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function checkLoggedUser(Request $request) {
    $token = '';
    if ($request->cookies->has('Drupal_visitor_personify_authorized')) {
      $token = $request->cookies->get('Drupal_visitor_personify_authorized');
    }
    if (empty($token)) {
      // Personify user not found. Redirect to login page.
      return new RedirectResponse(Url::fromRoute('openy_gc_auth_personify.api_login_personify', [''])->toString());
    }

    if (!$this->userHasActiveMembership($token)) {
      user_cookie_delete('personify_authorized');
      user_cookie_delete('personify_time');

      $path = URL::fromUserInput(
        $this->configFactory->get('openy_gated_content.settings')->get('virtual_y_login_url'),
        ['query' => ['personify-error' => '1']]
      )->toString();
      return new RedirectResponse($path);
    }

    // {"UserExists":true|false,"UserName":"","Email":"","DisableAccountFlag":false|true}.
    $userInfo = $this->personifySSO->getCustomerInfo($token);

    if (!empty($userInfo) && !empty($userInfo['UserExists']) && empty($userInfo['DisableAccountFlag'])) {
      $name = !empty($userInfo['UserName']) ? $userInfo['UserName'] : '';
      $email = !empty($userInfo['Email']) ? $userInfo['Email'] : '';

      // Authorize user (register, login, log, etc).
      $this->gcUserAuthorizer->authorizeUser($name, $email);

      return new RedirectResponse($this->configFactory->get('openy_gated_content.settings')->get('virtual_y_url'));
    }

    user_cookie_delete('personify_authorized');
    user_cookie_delete('personify_time');

    $path = URL::fromUserInput(
      $this->configFactory->get('openy_gated_content.settings')->get('virtual_y_login_url'),
      ['query' => ['personify-error' => '1']]
    )->toString();
    return new RedirectResponse($path);
  }

  /**
   * Logout actions and redirect.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response with logout redirect url.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function signOutUrl(Request $request) {
    user_cookie_delete('personify_authorized');
    user_cookie_delete('personify_time');

    $query = $request->query->all();
    $redirect_url = Url::fromRoute('<front>')->toString();
    if (isset($query['dest'])) {
      $redirect_url = urldecode($query['dest']);
    }

    return new JsonResponse($redirect_url);
  }

  /**
   * Check for user active membership.
   *
   * @param string $token
   *   Personify token.
   *
   * @return bool
   *   Whether user has active membership or not.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function userHasActiveMembership($token) {
    $personifyID = $this->personifySSO->getCustomerIdentifier($token);
    if (empty($personifyID)) {
      return FALSE;
    }

    $body = '<StoredProcedureRequest>
    <StoredProcedureName>usr_OpenY_Member_Access</StoredProcedureName>
    <SPParameterList>
      <StoredProcedureParameter>
        <Name>ip_org_id</Name>
        <Value>YMCASV</Value>
        <Direction>1</Direction>
      </StoredProcedureParameter>
      <StoredProcedureParameter>
        <Name>ip_org_unit_id</Name>
        <Value>YMCASV</Value>
        <Direction>1</Direction>
      </StoredProcedureParameter>
      <StoredProcedureParameter>
        <Name>ip_master_customer_id</Name>
        <Value>' . $personifyID . '</Value>
        <Direction>1</Direction>
      </StoredProcedureParameter>
      <StoredProcedureParameter>
        <Name>ip_sub_customer_id</Name>
        <Value>0</Value>
        <Direction>1</Direction>
      </StoredProcedureParameter>
      <StoredProcedureParameter>
        <Name>ip_access_type</Name>
        <Value>Virtual</Value>
        <Direction>1</Direction>
      </StoredProcedureParameter>
    </SPParameterList>
    </StoredProcedureRequest>';

    $data = $this->personifyClient->doAPIcall('POST', 'GetStoredProcedureDataJSON?$format=json', $body, 'xml');

    if ($data) {
      $results = json_decode($data['Data'], TRUE);

      if (isset($results['Table'][0]['Access']) && (strtolower($results['Table'][0]['Access']) === 'approved')) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Login user to Personify.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function apiLogin(Request $request) {
    $options = [
      'absolute' => TRUE,
      'query' => [
        'dest' => urlencode(Url::fromRoute('openy_gc_auth_personify.personify_auth')->toString()),
      ],
    ];

    // Generate auth URL that would base of validation token.
    $url = Url::fromRoute('openy_gc_auth_personify.personify_auth', [], $options)->toString();

    $vendor_token = $this->personifySSO->getVendorToken($url);
    $options = [
      'query' => [
        'vi' => $this->personifySSO->getConfigVendorId(),
        'vt' => $vendor_token,
      ],
    ];

    $env = $this->configFactory->get('personify.settings')->get('environment');
    $configLoginUrl = $this->configFactory->get('openy_gc_auth_personify.settings')->get($env . '_url_login');

    if (empty($configLoginUrl)) {
      $this->messenger->addWarning('Please, check Personify configs in settings.php.');
      return NULL;
    }

    $loginUrl = Url::fromUri($configLoginUrl, $options)->toString();
    $redirect = new TrustedRedirectResponse($loginUrl);
    $redirect->send();

    exit();
  }

}
