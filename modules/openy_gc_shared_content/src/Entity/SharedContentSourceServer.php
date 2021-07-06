<?php

namespace Drupal\openy_gc_shared_content\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Shared Content Source server entity.
 *
 * @ConfigEntityType(
 *   id = "shared_content_source_server",
 *   label = @Translation("Shared Content Source Server"),
 *   handlers = {
 *     "list_builder" = "Drupal\openy_gc_shared_content\Entity\SharedContentSourceServerListBuilder",
 *     "form" = {
 *       "add" = "Drupal\openy_gc_shared_content\Form\SharedContentSourceServerForm",
 *       "edit" = "Drupal\openy_gc_shared_content\Form\SharedContentSourceServerForm",
 *       "fetch" = "Drupal\openy_gc_shared_content\Form\SharedContentFetchForm",
 *       "delete" = "Drupal\openy_gc_shared_content\Form\SharedContentSourceServerDeleteForm",
 *     }
 *   },
 *   config_prefix = "shared_content_source_server",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "url" = "url",
 *     "token" = "token",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "url" = "url",
 *     "token" = "token",
 *   },
 *   links = {
 *     "edit-form" = "/admin/virtual-y/shared-content-server/{shared_content_source_server}",
 *     "fetch-form" = "/admin/virtual-y/shared-content-server/fetch/{shared_content_source_server}",
 *     "delete-form" = "/admin/virtual-y/shared-content-server/{shared_content_source_server}/delete",
 *   }
 * )
 */
class SharedContentSourceServer extends ConfigEntityBase implements SharedContentSourceServerInterface {

  /**
   * The Shared Content Source ID.
   *
   * @var string
   */
  public $id;

  /**
   * The Shared Content Source label.
   *
   * @var string
   */
  public $label;

  /**
   * The Shared Content Source url.
   *
   * @var string
   */
  public $url;

  /**
   * The Shared Content Source token.
   *
   * @var string
   */
  public $token;

  /**
   * {@inheritdoc}
   */
  public function label() {
    $label = parent::label();
    return $label ?: $this->id();
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * {@inheritdoc}
   */
  public function getToken() {
    return $this->token;
  }

}
