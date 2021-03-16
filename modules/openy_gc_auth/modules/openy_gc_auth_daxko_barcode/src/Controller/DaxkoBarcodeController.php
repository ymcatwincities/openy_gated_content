<?php

namespace Drupal\openy_gc_auth_daxko_barcode\Controller;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\openy_gc_auth\GCUserAuthorizer;

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
   * The messenger service.
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
   * DaxkoBarcodeController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger factory.
   * @param \GuzzleHttp\Client $http_client
   *   HTTP client.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\openy_gc_auth\GCUserAuthorizer $gcUserAuthorizer
   *   The Gated User Authorizer.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    LoggerChannelFactoryInterface $loggerChannelFactory,
    Client $http_client,
    MessengerInterface $messenger,
    GCUserAuthorizer $gcUserAuthorizer
  ) {
    $this->configFactory = $configFactory;
    $this->logger = $loggerChannelFactory->get('openy_gc_auth_daxko_barcode');
    $this->httpClient = $http_client;
    $this->messenger = $messenger;
    $this->gcUserAuthorizer = $gcUserAuthorizer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('logger.factory'),
      $container->get('http_client'),
      $container->get('messenger'),
      $container->get('openy_gc_auth.user_authorizer')
    );
  }

  /**
   * Validate whether user has a valid barcode.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request object.
   * @param string $barcode
   *   Daxko barcode.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function validate(Request $request, $barcode) {
    $config = $this->configFactory->get('openy_gc_auth.provider.daxko_barcode');

    // First make sure we have barcode in $request.
    if (empty($barcode)) {
      $this->messenger->addError('No barcode passed. Please try again.');
      return new RedirectResponse($this->configFactory->get('openy_gated_content.settings')->get('virtual_y_login_url'));
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

            $name = 'daxkoBarcode+' . $barcode;
            $email = $name . '@virtualy.openy.org';

            // Authorize user (register, login, log, etc).
            $this->gcUserAuthorizer->authorizeUser($name, $email);

            return new RedirectResponse($this->configFactory->get('openy_gated_content.settings')->get('virtual_y_url'));

          case 'not_found':
          case 'access_denied':
          case 'duplicate_barcode':
          case 'invalid':

            $this->messenger->addError($config->get("message_{$status}") . ' ' . $config->get('message_help'));
            return new RedirectResponse($this->configFactory->get('openy_gated_content.settings')->get('virtual_y_login_url'));
        }

      }
      else {
        $this->messenger->addError('Signature check failed.');
        return new RedirectResponse($this->configFactory->get('openy_gated_content.settings')->get('virtual_y_login_url'));
      }
    }
    else {
      $this->messenger->addError($config->get('message_invalid'));
      return new RedirectResponse($this->configFactory->get('openy_gated_content.settings')->get('virtual_y_login_url'));
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

    $now = round(microtime(TRUE) * 1000);
    if ($now > $dax_expiration) {
      return FALSE;
    }

    $input_string = $dax_expiration . $status . $area_id;
    $key = hex2bin($validation_secret);
    $our_signature = strtoupper(hash_hmac("sha256", $input_string, $key));

    return hash_equals($our_signature, $dax_signature);
  }

}
