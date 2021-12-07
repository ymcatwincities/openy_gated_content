<?php

namespace Drupal\openy_gc_shared_content\Controller;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\openy_gc_shared_content\Entity\SharedContentSourceServerInterface;
use Drupal\user\UserDataInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Preview Controller for SharedContentFetchForm.
 */
class PreviewController extends ControllerBase {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * The plugin manager for SharedContentSourceType classes.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $sharedSourceTypeManager;

  /**
   * The user data interface.
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $userData;

  /**
   * {@inheritdoc}
   */
  public function __construct(FormBuilder $formBuilder, PluginManagerInterface $manager, UserDataInterface $user_data) {
    $this->formBuilder = $formBuilder;
    $this->sharedSourceTypeManager = $manager;
    $this->userData = $user_data;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('plugin.manager.shared_content_source_type'),
      $container->get('user.data')
    );
  }

  /**
   * Callback for opening the modal form.
   */
  public function openPreviewModal(Request $request, SharedContentSourceServerInterface $shared_content_source_server, string $type, string $uuid) {
    // Prepare AjaxResponse.
    $response = new AjaxResponse();

    // Update the previewed list with this UUID if it's not already there.
    $previewed = $this->userData->get(
      'openy_gc_shared_content',
      $this->currentUser()->id(),
      $shared_content_source_server->id() . '_previewed'
    );
    if (!in_array($uuid, $previewed)) {
      $previewed[] = $uuid;
    }
    $this->userData->set('openy_gc_shared_content',
      $this->currentUser()->id(),
      $shared_content_source_server->id() . '_previewed',
      $previewed
    );

    // Prepare preview in $content.
    $instance = $this->sharedSourceTypeManager->createInstance($type);
    $query_args = $instance->getFullJsonApiQueryArgs();
    $data = $instance->jsonApiCall($shared_content_source_server, $query_args, $uuid);
    $content = $instance->formatItem($data, FALSE);
    $content['#server'] = $shared_content_source_server->getUrl();

    // Prepare the form and send everything we need as extra arguments.
    $form = $this->formBuilder->getForm('Drupal\openy_gc_shared_content\Form\SharedContentPreviewForm', $content, $type, $uuid);

    // Open the modal with the content preview and the "fetch" form.
    $response->addCommand(new OpenModalDialogCommand(
      'Preview',
      [$content, $form],
      ['width' => '900']
    ));
    return $response;
  }

}
