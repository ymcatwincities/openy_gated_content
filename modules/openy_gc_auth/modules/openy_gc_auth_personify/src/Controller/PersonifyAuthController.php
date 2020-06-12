<?php

namespace Drupal\openy_gc_auth_personify\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\personify\PersonifyClient;
use Drupal\personify\PersonifySSO;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
   */
  public function __construct(
    PersonifySSO $personifySSO,
    PersonifyClient $personifyClient,
    ConfigFactoryInterface $configFactory,
    LoggerChannelFactory $loggerChannelFactory
  ) {
    $this->personifySSO = $personifySSO;
    $this->personifyClient = $personifyClient;
    $this->configFactory = $configFactory;
    $this->logger = $loggerChannelFactory->get('openy_gc_auth_personify');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('personify.sso_client'),
      $container->get('personify.client'),
      $container->get('config.factory'),
      $container->get('logger.factory')
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
        $errorMessage = NULL;
        user_cookie_save([
          'personify_authorized' => $token,
          'personify_time' => REQUEST_TIME,
        ]);
      }
    }

    // Failed auth attempt.
    if ($errorMessage) {
      $this->logger->warning($errorMessage);
    }

    $redirect_url = Url::fromRoute('<front>')->toString();
    if (isset($query['dest'])) {
      $redirect_url = urldecode($query['dest']);
    }

    $redirect = new TrustedRedirectResponse($redirect_url);
    $redirect->send();

    exit();
  }

  /**
   * Check whether user is already logged in to Personify.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response with user data or error.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function checkLoggedUser() {
    $token = !empty($_COOKIE['Drupal_visitor_personify_authorized']) ? $_COOKIE['Drupal_visitor_personify_authorized'] : '';
    if (empty($token)) {
      return new JsonResponse([
        'message' => 'Personify user not found. Redirect to login page.',
        'user' => [],
      ]);
    }

    if (!$this->userHasActiveMembership($token)) {
      user_cookie_delete('personify_authorized');
      user_cookie_delete('personify_time');

      return new JsonResponse([
        'message' => 'Personify user does\'n have active membership.',
        'user' => [],
      ], 403);
    }

    // {"UserExists":true|false,"UserName":"","Email":"","DisableAccountFlag":false|true}.
    $userInfo = $this->personifySSO->getCustomerInfo($token);

    if (!empty($userInfo) && !empty($userInfo['UserExists']) && empty($userInfo['DisableAccountFlag'])) {
      $username = !empty($userInfo['UserName']) ? $userInfo['UserName'] : '';
      $email = !empty($userInfo['Email']) ? $userInfo['Email'] : '';

      return new JsonResponse([
        'message' => 'success',
        'user' => [
          'email' => $username,
          'name' => $email,
          'primary' => 1,
        ],
      ]);
    }

    user_cookie_delete('personify_authorized');
    user_cookie_delete('personify_time');

    return new JsonResponse([
      'message' => 'Personify user is found, but marked as not existed or disabled.',
      'user' => [],
    ], 403);
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

    $memberships = $this->personifyClient->doAPIcall(
      'GET',
      'OrderMembershipInformationViews?$filter=ShipMasterCustomerId%20eq%20%27' . $personifyID . '%27&$format=json'
    );
    if (!isset($memberships['d'])) {
      return FALSE;
    }

    $isActive = FALSE;
    foreach ($memberships['d'] as $m) {
      // "LineStatusDescr"=>"Active", "FulfillStatusDescr"=>"Active|Expired".
      if ($m['LineStatusDescr'] == 'Active' && $m['FulfillStatusDescr'] == 'Active') {
        $isActive = TRUE;
        break;
      }
    }

    return $isActive;
  }

}
