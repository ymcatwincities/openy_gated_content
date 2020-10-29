<?php

namespace Drupal\openy_gc_auth_reclique;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\Client;

/**
 * Class RecliqueClientService.
 *
 * @package Drupal\openy_gc_auth_reclique
 */
class RecliqueClientService {

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   ConfigFactoryInterface instance.
   * @param \GuzzleHttp\Client $client
   *   Guzzle client.
   */
  public function __construct(ConfigFactoryInterface $configFactory, Client $client) {
    $this->configFactory = $configFactory;
    $this->client = $client;
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
    $provider_config = $this->configFactory->get('openy_gc_auth.provider.reclique');

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
      $response = $this->client->request('POST', $provider_config->get('verification_url'), $options);

      if ($response->getStatusCode() == '200') {
        $content = $response->getBody()->getContents();
        return json_decode($content, TRUE);
      }
    }
    catch (\Exception $e) {
      $this->logger('openy_gated_content')->error($e->getMessage());
    }
    return [];
  }

}
