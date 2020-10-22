<?php

namespace Drupal\openy_gc_auth_personify\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\openy_gc_auth\Event\GCUserLogoutEvent;
use GuzzleHttp\Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class PersonifyUserLogoutSubscriber Subscriber.
 *
 * @package Drupal\openy_gc_auth_personify\EventSubscriber
 */
class PersonifyUserLogoutSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $currentRequest;

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
   * The Http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * Constructs a new PersonifyUserLogoutSubscriber.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerChannelFactory
   *   Logger factory.
   * @param \GuzzleHttp\Client $client
   *   The Http client.
   */
  public function __construct(
    RequestStack $requestStack,
    ConfigFactoryInterface $configFactory,
    LoggerChannelFactory $loggerChannelFactory,
    Client $client
  ) {
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->configFactory = $configFactory;
    $this->logger = $loggerChannelFactory->get('openy_gc_auth_personify');
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      // Static class constant => method on this class.
      GCUserLogoutEvent::EVENT_NAME => 'onUserLogout',
    ];
  }

  /**
   * Subscribe to the GC user logout event dispatched.
   *
   * @param \Drupal\openy_gc_auth\Event\GCUserLogoutEvent $event
   *   Event object.
   */
  public function onUserLogout(GCUserLogoutEvent $event) {
    $token = '';
    if ($this->currentRequest->cookies->has('Drupal_visitor_personify_authorized')) {
      $token = $this->currentRequest->cookies->get('Drupal_visitor_personify_authorized');
    }
    if (empty($token)) {
      return FALSE;
    }

    $isUserSuccessfullyLogout = $this->apiLogout($token);
    if ($isUserSuccessfullyLogout) {
      user_cookie_delete('personify_authorized');
      user_cookie_delete('personify_time');
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Logout user from Personify.
   *
   * @param string $customerToken
   *   Personify customer's token.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function apiLogout($customerToken) {
    $settings = $this->configFactory->get('personify.settings');
    $env = $settings->get('environment');

    $options = [
      'headers' => [
        'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8',
        'User-Agent' => '',
      ],
      'auth' => [
        $settings->get($env . 'username'),
        $settings->get($env . 'password'),
      ],
      'verify' => FALSE,
      'form_params' => [
        'vendorUsername' => $settings->get('vendor_username'),
        'vendorPassword' => $settings->get('vendor_password'),
        'customerToken' => $customerToken,
      ],
    ];

    try {

      $endpoint = $this->configFactory->get('openy_gc_auth_personify.settings')->get($env . '_url_logout');

      $response = $this->client->request('POST', $endpoint, $options);

      if ($response->getStatusCode() != '200') {
        $this->logger->error($this->t('Failed attempt to logout a user from Personify'));
        return FALSE;
      }

      return TRUE;
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
    return FALSE;
  }

}
