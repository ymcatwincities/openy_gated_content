<?php

namespace Drupal\openy_gated_content;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\recurring_events\Entity\EventInstance;
use Drupal\recurring_events\Entity\EventSeries;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class Segment Content Access Check.
 *
 * @package Drupal\openy_gated_content
 */
class SegmentContentAccessCheck implements ContainerInjectionInterface {

  use VirtualYAccessTrait;

  /**
   * Module config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */

  protected $config;

  /**
   * The currently active request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * SegmentContentAccessCheck constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   ConfigFacroty service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request stack.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ConfigFactoryInterface $configFactory, RequestStack $request, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler) {
    $this->config = $configFactory->get('openy_gated_content.settings');
    $this->request = $request->getCurrentRequest();
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('module_handler')
    );
  }

  /**
   * Virtual Y check access logic.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to check.
   * @param string $operation
   *   Operation type.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Current account object.
   *
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden|\Drupal\Core\Access\AccessResultNeutral
   *   Access result object.
   */
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    $permissions_config = $this->getEnabledEntities();

    // Our code override's default view permission only.
    if ($operation !== 'view') {
      return AccessResult::neutral();
    }

    $type = $entity->getEntityTypeId();

    // Skip other entities.
    if (!array_key_exists($type, $permissions_config)) {
      return AccessResult::neutral();
    }

    $bundle = $entity->bundle();

    if (!in_array($bundle, $permissions_config[$type])) {
      return AccessResult::neutral();
    }

    // Check if this request from shared content client server.
    if ($this->request->headers->get('x-shared-content')) {
      // For performance reason first check by x-shared-content.
      if ($this->isValidSharedClient()) {
        // Bypass permissions for shared content client servers.
        return AccessResult::allowed();
      }
    }

    $account_roles = $account->getRoles();

    // Use Drupal permissions for administrators and editors.
    $privileged_roles = array_merge($this->getVirtualyEditorRoles(), ['administrator']);
    if (!empty(array_intersect($privileged_roles, $account_roles))) {
      return AccessResult::neutral();
    }

    $content_access_mask = $entity->get('field_vy_permission')->getValue();

    // For Eventinstance we have to check parent as well.
    if (($entity instanceof EventInstance) && empty($content_access_mask)) {
      $eventSeriesEntity = $entity->getEventSeries();
      if ($eventSeriesEntity instanceof EventSeries) {
        $content_access_mask = $eventSeriesEntity->get('field_vy_permission')
          ->getValue();
      }
    }

    // Deny access if editor haven't set up permission field value.
    if (empty($content_access_mask)) {
      return AccessResult::forbidden();
    }

    // Get roles, available for this user.
    $available_roles = explode(',', $content_access_mask[0]['value']);

    foreach ($account_roles as $account_role) {
      if (in_array($account_role, $available_roles)) {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }

  /**
   * Validate request from shared content.
   *
   * @return bool
   *   TRUE if request is valid.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function isValidSharedClient() {
    // Validate by field_gc_share.
    $query = $this->request->query->all();
    if (strstr($this->request->getPathInfo(), 'jsonapi') && !isset($query['filter']['field_gc_share']) && !(bool) $query['filter']['field_gc_share']) {
      return FALSE;
    }

    $url = $this->request->headers->get('x-shared-referer');
    $token = $this->request->headers->get('authorization');
    if (!$url || !$token) {
      return FALSE;
    }

    if (!$this->moduleHandler->moduleExists('openy_gc_shared_content')) {
      // This should work only if openy_gc_shared_content enabled.
      return FALSE;
    }

    if ($this->moduleHandler->moduleExists('openy_gc_shared_content_server')) {
      // On shared content server we should search by shared_content_source.
      $server_entity_type = 'shared_content_source';
    }
    else {
      // On client server we should search by shared_content_source_server.
      $server_entity_type = 'shared_content_source_server';
    }
    $entity_storage = $this->entityTypeManager->getStorage($server_entity_type);
    $entities = $entity_storage->loadByProperties([
      'url' => $url,
      'token' => str_replace('Bearer ', '', $token),
    ]);
    if (empty($entities)) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Helper config function that shows Virtual Y entities and bundles.
   *
   * @return array
   *   Config array.
   */
  private function getEnabledEntities() {
    $return = [];
    $rawConfig = $this->config->getRawData();
    if (!empty($rawConfig['permissions_entities'])) {
      $return = $rawConfig['permissions_entities'];
    }
    return $return;
  }

}
