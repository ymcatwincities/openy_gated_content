<?php

namespace Drupal\openy_gc_auth_personify;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use GuzzleHttp\Client as HttpClient;

/**
 * Personify logout client.
 */
class LogoutClient {

  use StringTranslationTrait;

  /**
   * Logger interface.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Personify Config.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Provider Config.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $providerConfig;

  /**
   * The Http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * Personify Client.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerChannelFactory
   *   Logger factory.
   * @param \GuzzleHttp\Client $client
   *   The Http client.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    LoggerChannelFactory $loggerChannelFactory,
    HttpClient $client
  ) {
    $this->config = $configFactory->get('personify.settings');
    $this->providerConfig = $configFactory->get('openy_gc_auth_personify.settings');
    $this->logger = $loggerChannelFactory->get('openy_gc_auth_personify');
    $this->client = $client;
  }

  /**
   * Personify Config.
   */
  public function getConfig() {
    return $this->config;
  }

  /**
   * Provider Config.
   */
  public function getProviderConfig() {
    return $this->providerConfig;
  }

  /**
   * Logout user from Personify.
   *
   * @param string $customerToken
   *   Personify customer's token.
   *
   * @return bool
   *   Returs TRUE when successfully logged out.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function logout($customerToken) {
    $env = $this->getConfig()->get('environment');

    $options = [
      'headers' => [
        'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8',
        'User-Agent' => '',
      ],
      'auth' => [
        $this->getConfig()->get($env . 'username'),
        $this->getConfig()->get($env . 'password'),
      ],
      'verify' => FALSE,
      'form_params' => [
        'vendorUsername' => $this->getConfig()->get('vendor_username'),
        'vendorPassword' => $this->getConfig()->get('vendor_password'),
        'customerToken' => $customerToken,
      ],
    ];

    try {
      $endpoint = $this->getProviderConfig()->get($env . '_url_logout');
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
