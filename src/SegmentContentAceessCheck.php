<?php

namespace Drupal\openy_gated_content;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SegmentContentAceessCheck implements ContainerInjectionInterface {

  protected $messenger;

  /**
   * Constructor.
   *
   * @param Drupal\Core\Messenger\Messenger $messenger
   *   The entity type manager.
   */
  public function __construct(Messenger $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger')
    );
  }

  public function checkAccess(EntityInterface $entity, $operation, AccountInterface  $account) {
    $i=1;

    

    if ($operation !== 'view') {
      return AccessResult::neutral();
    }

    $type = $entity->getEntityTypeId();

    $permissions_config = $this->getEnabledEntities();
    if (key_exists($type, $permissions_config)) {
      $bundle = $entity->bundle();

      if (in_array($bundle, $permissions_config[$type])) {
        $content_access_mask = $entity->get('field_vy_permission')->getValue();
        $available_roles = explode(',', $content_access_mask[0]['value']);
        $account_roles = $account->getRoles();
        foreach ($account_roles as $account_role) {
          if (in_array($account_role, $available_roles)) {
            return AccessResult::allowed();
          }
        }
        return AccessResult::forbidden();

      }

    } else {
      return AccessResult::neutral();
    }

  }

  private function getEnabledEntities() {
    return [
      'node' => [
         'gc_video',
         'vy_blog_post'
       ],
      'eventinstance' => [
        'live_stream',
        'virtual_meeting'
      ],
      'eventseries' => [
        'live_stream',
        'virtual_meeting'
      ]
    ];
  }


}