<?php

namespace Drupal\openy_gated_content;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;

/**
 * Common service for GC related user stuff.
 *
 * @package Drupal\openy_gated_content
 */
class GCUserService {

  use VirtualYAccessTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Mail manager service.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * GCUserService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   Mail manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, MailManagerInterface $mail_manager, LanguageManagerInterface $language_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->mailManager = $mail_manager;
    $this->languageManager = $language_manager;
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

  /**
   * Send welcome email.
   *
   * @param string $key
   *   A key to identify the email sent.
   * @param string $to
   *   The email address or addresses where the message will be sent to.
   * @param array $params
   *   Parameters to build the email.
   *
   * @return array
   *   The $message array structure containing all details of the message.
   */
  public function sendEmail(string $key, string $to, array $params = []): array {
    return $this->mailManager->mail(
      'openy_gated_content',
      $key,
      $to,
      $this->languageManager->getDefaultLanguage()->getId(),
      $params
    );
  }

}
