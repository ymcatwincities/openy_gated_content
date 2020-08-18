<?php

namespace Drupal\openy_gc_shared_content\Controller;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\openy_gc_shared_content\Entity\SharedContentSourceServerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Preview Controller for SharedContentFetchForm.
 */
class PreviewController extends ControllerBase {

  /**
   * The plugin manager for SharedContentSourceType classes.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $sharedSourceTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(PluginManagerInterface $manager) {
    $this->sharedSourceTypeManager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.shared_content_source_type')
    );
  }

  /**
   * Callback for opening the modal form.
   */
  public function openPreviewModal(Request $request, SharedContentSourceServerInterface $shared_content_source_server, string $type, string $uuid) {
    $response = new AjaxResponse();
    $instance = $this->sharedSourceTypeManager->createInstance($type);
    $query_args = $instance->getFullJsonApiQueryArgs();
    $data = $instance->jsonApiCall($shared_content_source_server->getUrl(), $query_args, $uuid);
    $content = $instance->formatItem($data, FALSE);
    $content['#server'] = $shared_content_source_server->getUrl();
    $response->addCommand(new OpenModalDialogCommand('Preview', $content, ['width' => '900']));

    return $response;
  }

}
