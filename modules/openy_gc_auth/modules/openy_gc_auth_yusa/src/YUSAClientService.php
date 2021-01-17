<?php

namespace Drupal\openy_gc_auth_yusa;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use GuzzleHttp\Client;

/**
 * Y-USA client wrapper service.
 *
 * @package Drupal\openy_gc_auth_yusa
 */
class YUSAClientService {

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
   * Http client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * RecliqueClientService constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerFactory
   *   LoggerChannelFactory instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   ConfigFactoryInterface instance.
   * @param \GuzzleHttp\Client $client
   *   Guzzle client.
   */
  public function __construct(LoggerChannelFactory $loggerFactory, ConfigFactoryInterface $configFactory, Client $client) {
    $this->logger = $loggerFactory->get('openy_gc_auth_yusa');
    $this->configFactory = $configFactory;
    $this->client = $client;
  }

  /**
   * Get user data from Y-USA.
   *
   * @param string $id
   *   User email.
   *
   * @return array|mixed
   *   User object array.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getUserData($id) {
    $provider_config = $this->configFactory->get('openy_gc_auth.provider.yusa');
    if ($provider_config->get('verification_type') == 'membership_id') {
      $request = [
        'AssociationNumber' => $provider_config->get('association_number'),
        'LocalMemberId' => $id,
      ];
    }
    if ($provider_config->get('verification_type') == 'email') {
      $request = [
        'AssociationNumber' => $provider_config->get('association_number'),
        'Email' => $id,
      ];
    }
    if ($provider_config->get('verification_type') == 'barcode') {
      $request = [
        'AssociationNumber' => $provider_config->get('association_number'),
        'Barcode' => $id,
      ];
    }

    $options = [
      'json' => $request,
      'headers' => [
        'Content-Type' => 'application/json;charset=utf-8',
      ],
      'auth' => [
        $provider_config->get('auth_login'),
        $provider_config->get('auth_pass'),
      ],
    ];

    try {
      $response = $this->client->request('POST', $provider_config->get('verification_url'), $options);

      if ($response->getStatusCode() == '200') {
        $content = $response->getBody()->getContents();
        return json_decode($content, TRUE);
      }
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }

    return [];

  }

}
