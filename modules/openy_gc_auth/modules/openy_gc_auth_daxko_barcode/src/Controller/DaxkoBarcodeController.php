<?php

namespace Drupal\openy_gc_auth_daxko_barcode\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Personify controller to handle Personify SSO authentication.
 */
class DaxkoBarcodeController extends ControllerBase {

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
   * DaxkoBarcodeController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerChannelFactory
   *   Logger factory.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    LoggerChannelFactory $loggerChannelFactory
  ) {
    $this->configFactory = $configFactory;
    $this->logger = $loggerChannelFactory->get('openy_gc_auth_daxko_barcode');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('logger.factory')
    );
  }

  /**
   * Validate whether user has a valid barcode.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response with user data or error.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function validate(Request $request) {
    \Drupal::logger('openy_gc_auth_daxko_barcode')->notice('Request received: %request', ['%request' => print_r(Json::decode($request->getContent()), true)]);
    $content = Json::decode($request->getContent());
    $barcode = $content['barcode'];

    $config = $this->configFactory->get('openy_gc_auth.provider.daxco_barcode');
    // URL has been validated on input so we know it's good.
    $action_url = $config->get('action_url');
    $validation_secret = $config->get('secret');

    \Drupal::logger('openy_gc_auth_daxko_barcode')->notice('Action: @action. Secret: @secret', [
      '@action' => $action_url,
      '@secret' => $validation_secret
    ]);

    // Not sure how we'd get here without a barcode, but just in case.
    if (empty($barcode)) {
      return new JsonResponse([
        'message' => 'No barcode passed. Please try again.',
        'user' => [],
      ], 403);
    }

    // Send the barcode to Daxko and parse the response.
    $dax_response = $this->getDaxkoResponse($action_url, $barcode);
    \Drupal::logger('openy_gc_auth_daxko_barcode')->notice('Dax response: @response', [
      '@response' => print_r($dax_response, TRUE)
    ]);
    $dax_expiration = $dax_response['daxExpiration'] ?? NULL;
    $dax_signature = $dax_response['daxSignature'] ?? NULL;
    $status = $dax_response['status'] ?? NULL;
    $area_id = $dax_response['area_id'] ?? NULL;

    // If all of the pieces are returned, then validate the response.
    if ($dax_expiration && $dax_signature && $status && $area_id) {
      // If everything validates then we can decide what response to send.
      if ($this->validDaxSignature($dax_expiration, $status, $area_id, $validation_secret, $dax_signature)) {
        switch ($status) {
          case 'success':
            return new JsonResponse([
              'message' => 'success',
              'user' => [
                'barcode' => $barcode,
                'primary' => 1,
              ],
            ]);
          case 'not_found':
          case 'access_denied':
          case 'duplicate_barcode':
          case 'invalid':
            return new JsonResponse([
              'message' => $config->get("message_{$status}"),
              'help' => $config->get('message_help'),
              'user' => [],
            ], 403);
        }

      }
    }
    else {
      return new JsonResponse([
        'message' => $config->get('message_invalid'),
        'user' => [],
      ], 403);
    }
  }

  /**
   * Send the user's barcode to Daxko and parse the response.
   *
   * @param string $action_url
   * @param string $barcode
   *
   * @return array
   *   An associative array of query strings from the response whose keys should include:
   *   status, daxExpiration, daxSignature, and area_id.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function getDaxkoResponse($action_url, $barcode) {
    // Set our options.
    $options = [
      'form_params' => [
        'mode' => 'custom',
        'barcode' => $barcode,
      ],
      'allow_redirects' => false,
    ];

    try {
      $client = \Drupal::httpClient();
      $request = $client->request('POST', $action_url, $options);
    }
    catch (RequestException $e) {
      // Log the error.
      watchdog_exception('openy_gc_auth_daxko_barcode', $e);
    }

    $response_status = $request->getStatusCode();
    if ($response_status == 302) {
      $location = $request->getHeader('Location');
      return UrlHelper::parse(array_shift($location))['query'];
    }
  }

  /**
   * Validate Daxko Barcode signature as per instructions here
   * https://github.com/daxko/dax-signature-validation.
   *
   * @param string $dax_expiration
   * @param string $status
   * @param string $area_id
   * @param string $validation_secret
   * @param string $dax_signature
   *
   * @return bool
   *   Whether the signature is validated or not.
   */
  private function validDaxSignature($dax_expiration, $status, $area_id, $validation_secret, $dax_signature) {

    $now = round(microtime(true)*1000);
    if ($now > $dax_expiration) {
      return FALSE;
    }

    $input_string = $dax_expiration . $status . $area_id;
    $key = hex2bin($validation_secret);
    $our_signature = strtoupper(hash_hmac("sha256", $input_string, $key));

    return hash_equals($our_signature, $dax_signature);
  }
}
