<?php

namespace Drupal\openy_gc_shared_content;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines the common interface for all SharedContentSourceType classes.
 */
interface SharedContentSourceTypeInterface extends PluginInspectionInterface {

  /**
   * Get plugin id.
   *
   * @return string
   *   The plugin_id of the plugin instance.
   */
  public function getId();

  /**
   * Get plugin title.
   *
   * @return string
   *   The label of the plugin instance.
   */
  public function getLabel();

  /**
   * Get entity machine name.
   *
   * @return string
   *   The entity machine name of the plugin instance (eg node, etc).
   */
  public function getEntityType();

  /**
   * Get entity bundle machine name.
   *
   * @return string
   *   The entity bundle machine name (eg vy_blog_post, gc_video, etc).
   */
  public function getEntityBundle();

  /**
   * Get Teaser JsonApi query arguments.
   *
   * Teaser request used for listing, that will be displayed in fetch form,
   * so here can be used base args, like sort, filter, etc.
   *
   * @return array
   *   List of query arguments that will be used in JsonApi request.
   */
  public function getTeaserJsonApiQueryArgs();

  /**
   * Get Full JsonApi query arguments.
   *
   * This request used for item preview or creating, so here you need to set
   * include args and filters.
   *
   * @return array
   *   List of query arguments that will be used in JsonApi request.
   */
  public function getFullJsonApiQueryArgs();

  /**
   * Get Excluded Relationships.
   *
   * JsonApi response data contains relationships section, that can conflict
   * with data on the site, for example node_type - it can has same name, but
   * different UUID, so during entity creating we will get error.
   * This method allows you to set list of such relationships and delete them
   * before entity creating.
   *
   * @return array
   *   List of field names that will be removed from JsonApi response data
   *   relationships.
   */
  public function getExcludedRelationships();

  /**
   * Get Excluded Relationships.
   *
   * JsonApi relationships field is entity references (taxonomy, media),
   * we need to process each reference separately (check for existing,
   * create entity). This method used to simplify of detecting such fields.
   *
   * @return array
   *   List of field names that will be processed for entity references
   *   creating.
   */
  public function getIncludedRelationships();

  /**
   * Checking for entity existing by UUID.
   *
   * @param string $uuid
   *   Entity UUID.
   *
   * @return bool
   *   TRUE if entity exists.
   */
  public function entityExists(string $uuid);

  /**
   * Process JSON API Data.
   *
   * This method used for processing JSON API response data before any actions
   * from plugin side. For example we can cleanup here some attributes (to
   * avoid conflicts with existing entities we should remove drupal internal id
   * and revision id).
   *
   * @param array $data
   *   JSON API response data.
   */
  public function processJsonApiData(array &$data);

  /**
   * Get JSON API Endpoint.
   *
   * Construct JSON API Endpoint based on plugin entity type and bundle.
   * If UUID not NULL - it will be attached to endpoint.
   *
   * @param string|null $uuid
   *   Entity UUID.
   *
   * @return string
   *   The internal JSON API Endpoint for request.
   */
  public function getJsonApiEndpoint($uuid = NULL);

  /**
   * JSON API request.
   *
   * This method used for data fetching from remote source server.
   *
   * @param string $url
   *   Server URL.
   * @param array $query_args
   *   Query arguments for JSON API request.
   * @param string|null $uuid
   *   Entity UUID.
   *
   * @return array
   *   Decoded JSON API response data.
   */
  public function jsonApiCall($url, array $query_args = [], $uuid = NULL);

}
