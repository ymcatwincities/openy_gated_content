<?php

namespace Drupal\openy_gc_auth_custom\Plugin\rest\resource;

use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Represents resource for Gated Content authentication provider.
 *
 * @RestResource(
 *   id = "openy_gc_auth_custom",
 *   label = @Translation("Custom authentication provider for Gated Content"),
 *   uri_paths = {
 *     "create" = "/openy-gc-auth/provider/custom/login"
 *   }
 * )
 */
class GatedContentCustomAuthentication extends ResourceBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The entity type targeted by this resource.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityTypeManager;

  /**
   * Email validate utility.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

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
   * @param \Drupal\Component\Utility\EmailValidatorInterface $validator
   *   Validates email addresses.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    array $serializer_formats,
    LoggerInterface $logger,
    EmailValidatorInterface $validator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entity_type_manager;
    $this->emailValidator = $validator;
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
      $container->get('email.validator')
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
    // TODO: validate recaptcha.
    if (!$this->emailValidator->isValid($content['email'])) {
      return $this->errorResponse($this->t('Email @email invalid.', [
        '@email' => $content['email'],
      ]), 400);
    }
    $gc_users = $this->entityTypeManager
      ->getStorage('gc_auth_custom_user')
      ->loadByProperties(['email' => $content['email']]);
    // Verify that the user exists.
    if (empty($gc_users)) {
      return $this->errorResponse($this->t('User with email @email not found.', [
        '@email' => $content['email'],
      ]), 404);
    }
    $user = reset($gc_users);
    // User can login.
    return new ModifiedResourceResponse([
      'message' => 'success',
      'user' => [
        'email' => $user->email->value,
        'name' => $user->first_name->value,
        'primary' => (bool) $user->primary->value,
      ],
      'status' => 'ok',
    ], 200);
  }

  /**
   * Error Response.
   *
   * @param string $message
   *   The error message.
   * @param int $status
   *   The HTTP status code for error.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   */
  protected function errorResponse($message, $status) {
    return new ModifiedResourceResponse([
      'message' => $message,
      'status' => 'invalid',
    ], $status);
  }

}
