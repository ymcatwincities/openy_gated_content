<?php

namespace Drupal\openy_gated_content;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\recurring_events\Entity\EventInstance;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class SegmentContentAccessCheck.
 *
 * @package Drupal\openy_gated_content
 */
class SegmentContentAccessCheck implements ContainerInjectionInterface {

  const EDITOR_ROLE = 'virtual_ymca_editor';

  /**
   * Module config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Private storage.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $privateTempStore;

  /**
   * The currently active request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * SegmentContentAccessCheck constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   ConfigFacroty service.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $private_temp_store
   *   Private Temp Store service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request stack
   */
  public function __construct(ConfigFactoryInterface $configFactory, PrivateTempStoreFactory $private_temp_store, RequestStack $request) {
    $this->config = $configFactory->get('openy_gated_content.settings');
    $this->privateTempStore = $private_temp_store->get('openy_gc_shared_content_temp');
    $this->request = $request->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('tempstore.private'),
      $container->get('request_stack')
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
    if (!in_array($type, array_keys($permissions_config))) {
      return AccessResult::neutral();
    }

    if (array_key_exists($type, $permissions_config)) {
      $bundle = $entity->bundle();

      if (in_array($bundle, $permissions_config[$type])) {

        $shared_content_client = $this->privateTempStore->get($this->request->getClientIp());
        if ($shared_content_client) {
          // Bypass permissions for shared content client servers.
          return AccessResult::neutral();
        }

        $account_roles = $account->getRoles();

        // Use Drupal permissions for administrators and editors.
        if (
          in_array(self::EDITOR_ROLE, $account_roles)
          || in_array('administrator', $account_roles)
        ) {
          return AccessResult::neutral();
        }

        $content_access_mask = $entity->get('field_vy_permission')->getValue();

        // For Eventinstance we have to check parent as well.
        if (($entity instanceof EventInstance) && empty($content_access_mask)) {
          $eventSeriesEntity = $entity->getEventSeries();
          $content_access_mask = $eventSeriesEntity->get('field_vy_permission')
            ->getValue();
        }

        // Deny access if editor didnt set up permission field value.
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

    }
    else {
      return AccessResult::neutral();
    }

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
