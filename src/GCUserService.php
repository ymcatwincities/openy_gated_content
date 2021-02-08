<?php

namespace Drupal\openy_gated_content;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SegmentContentAccessCheck.
 *
 * @package Drupal\openy_gated_content
 */
class GCUserService implements ContainerInjectionInterface {

  use VirtualYAccessTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * GCUserService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Get list of Virtual Y roles.
   */
  public function getRoles() {
    $roles = [];
    foreach ($this->entityTypeManager->getStorage('user_role')->loadMultiple() as $role_name => $role) {
      if (strpos($role_name, 'virtual_y') !== FALSE && (!in_array($role_name, $this->getVirtualyEditorRoles()))) {
        $roles[$role_name] = $role->label();
      }
    }
    return $roles;
  }

}
