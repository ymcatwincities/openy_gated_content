<?php

namespace Drupal\openy_gc_auth;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines the common interface for all GCIdentityProvider plugins.
 *
 * @see \Drupal\openy_gc_auth\GCIdentityProviderManager
 * @see \Drupal\openy_gc_auth\Annotation\GCIdentityProvider
 * @see plugin_api
 */
interface GCIdentityProviderInterface extends PluginInspectionInterface, ConfigurableInterface, PluginFormInterface {

  /**
   * Get GCIdentityProvider plugin id.
   */
  public function getId();

  /**
   * Get GCIdentityProvider plugin label.
   */
  public function getLabel();

  /**
   * Get plugin configuration name.
   */
  public function getConfigName();

  /**
   * Get login form content for auth method.
   *
   * @return array
   *   Render array for the form.
   */
  public function getLoginForm();

  /**
   * Get real user email.
   *
   * Some providers can store fake email in system, in this case method
   * should implement logic with getting real user email.
   *
   * @param int $uid
   *   User ID.
   *
   * @return string
   *   User email.
   */
  public function getMemberNotificationEmail(int $uid): string;

}
