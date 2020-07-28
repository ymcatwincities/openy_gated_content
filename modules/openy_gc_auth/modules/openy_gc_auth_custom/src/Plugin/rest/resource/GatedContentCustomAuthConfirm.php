<?php

namespace Drupal\openy_gc_auth_custom\Plugin\rest\resource;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\openy_gc_auth_custom\Plugin\rest\ErrorResponseTrait;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Represents resource for Virtual YMCA authentication provider.
 *
 * @RestResource(
 *   id = "openy_gc_auth_custom_confirm",
 *   label = @Translation("Email confirmation for Custom authentication provider"),
 *   uri_paths = {
 *     "create" = "/openy-gc-auth/provider/custom/login-by-link"
 *   }
 * )
 */
class GatedContentCustomAuthConfirm extends ResourceBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;
  use ErrorResponseTrait;

  /**
   * The entity type targeted by this resource.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a GatedContentCustomAuthentication.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    array $serializer_formats,
    LoggerInterface $logger,
    ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('config.factory')
    );
  }

  /**
   * Responds to POST requests and login custom user by email.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request object.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function post(Request $request) {
    $content = json_decode($request->getContent(), TRUE);
    if (!is_array($content) || empty($content)) {
      return $this->errorResponse($this->t('Bad Request.'), 400);
    }

    $provider_config = $this->configFactory->get('openy_gc_auth.provider.custom');
    $gc_user = $this->entityTypeManager
      ->getStorage('gc_auth_custom_user')
      ->loadByProperties([
        'id' => $content['id'],
        'verification_token' => $content['token'],
      ]);

    // Verify that the user exists.
    if (empty($gc_user)) {
      return $this->errorResponse($this->t('Invalid link. Please try to Sign in and verify your email one more time.'), 404);
    }

    $gc_user = reset($gc_user);
    if ($gc_user->getVerificationTime() + (int) $provider_config->get('email_verification_link_life_time') < time()) {
      return $this->errorResponse($this->t('Verification link has expired. Please try to Sign in and verify your email one more time.'), 400);
    }

    $gc_user->activate()->save();

    // User can login.
    return new ModifiedResourceResponse([
      'message' => 'success',
      'user' => [
        'email' => $gc_user->email->value,
        'name' => $gc_user->first_name->value,
        'primary' => (bool) $gc_user->primary->value,
      ],
      'status' => 'ok',
    ], 200);
  }

}
