<?php

namespace Drupal\openy_gc_auth_daxko_barcode\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use ReCaptcha\ReCaptcha;
use ReCaptcha\RequestMethod\Drupal8Post;
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
   * The Guzzle HTTP Client service.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * DaxkoBarcodeController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerChannelFactory
   *   Logger factory.
   * @param \GuzzleHttp\Client $http_client
   *   HTTP client.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    LoggerChannelFactory $loggerChannelFactory,
    Client $http_client
  ) {
    $this->configFactory = $configFactory;
    $this->logger = $loggerChannelFactory->get('openy_gc_auth_daxko_barcode');
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('logger.factory'),
      $container->get('http_client')
    );
  }

  /**
   * Validate whether user has a valid barcode.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response with user data or error.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function validate(Request $request) {
    $content = Json::decode($request->getContent());
    $config = $this->configFactory->get('openy_gc_auth.provider.daxco_barcode');

    // First make sure we got something in our $request.
    if (!is_array($content) || empty($content)) {
      return new JsonResponse(['message' => $this->t('Bad Request.')], 400);
    }

    $barcode = $content['barcode'];
    // Not sure how we'd get here without a barcode, but just in case.
    if (empty($barcode)) {
      return new JsonResponse([
        'message' => 'No barcode passed. Please try again.',
        'user' => [],
      ], 403);
    }

    // Validate recaptchaToken if enabled in the provider config.
    if ($config->get('enable_recaptcha')) {
      $validation_result = $this->validateRecaptcha($content, $request);
      if ($validation_result instanceof JsonResponse) {
        return $validation_result;
      }
    }

    $validation_secret = $config->get('secret');
    // Action URL has been validated on input so we can trust it's well-formed.
    $action_url = $config->get('action_url');

    // Send the barcode to Daxko and parse the response.
    $dax_response = $this->getDaxkoResponse($action_url, $barcode);
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

            return new JsonResponse(
              [
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
   *   Action url.
   * @param string $barcode
   *   Barcode value.
   *
   * @return array
   *   An assoc array of query strings from the response, should include:
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
      'allow_redirects' => FALSE,
    ];

    try {
      $client = $this->httpClient;
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
   * Validate based on https://github.com/daxko/dax-signature-validation.
   *
   * @param string $dax_expiration
   *   Daxko expiration time.
   * @param string $status
   *   Status value.
   * @param string $area_id
   *   Area id.
   * @param string $validation_secret
   *   Secret for Daxko.
   * @param string $dax_signature
   *   Signature value.
   *
   * @return bool
   *   Whether the signature is validated or not.
   */
  private function validDaxSignature($dax_expiration, $status, $area_id, $validation_secret, $dax_signature) {

    $now = round(microtime(TRUE)*1000);
    if ($now > $dax_expiration) {
      return FALSE;
    }

    $input_string = $dax_expiration . $status . $area_id;
    $key = hex2bin($validation_secret);
    $our_signature = strtoupper(hash_hmac("sha256", $input_string, $key));

    return hash_equals($our_signature, $dax_signature);
  }

  /**
   * Helper function for reCaptcha validation.
   *
   * @param array $content
   *   Input data from user
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object
   *
   * @return bool|JsonResponse
   *   True if success, or an error message if failure.
   */
  protected function validateRecaptcha(array $content, Request $request) {
    if (!$content['recaptchaToken']) {
      return new JsonResponse(
        ['message' => $this->t('ReCaptcha token required.')],
        400);
    }

    $config = $this->configFactory->get('recaptcha.settings');
    $recaptcha_secret_key = $config->get('secret_key');
    $recaptcha = new ReCaptcha($recaptcha_secret_key, new Drupal8Post());
    if ($config->get('verify_hostname')) {
      $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME']);
    }
    $resp = $recaptcha->verify($content['recaptchaToken'], $request->getClientIp());
    if (!$resp->isSuccess()) {
      return new JsonResponse(
        ['message' => $this->t('ReCaptcha token invalid.')],
        400);
    }

    return TRUE;
  }

}
