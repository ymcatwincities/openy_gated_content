<?php

namespace Drupal\openy_gc_shared_content\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Implements the ModalForm form controller.
 *
 * This example demonstrates implementation of a form that is designed to be
 * used as a modal form.  To properly display the modal the link presented by
 * the \Drupal\form_api_example\Controller\Page page controller loads the Drupal
 * dialog and ajax libraries.  The submit handler in this class returns ajax
 * commands to replace text in the calling page after submission .
 *
 * @see \Drupal\Core\Form\FormBase
 */
class SharedContentPreviewForm extends FormBase {

  /**
   * The plugin manager for SharedContentSourceType classes.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $sharedSourceTypeManager;

  /**
   * The Symfony request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public function __construct(PluginManagerInterface $manager, RequestStack $request_stack) {
    $this->sharedSourceTypeManager = $manager;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('plugin.manager.shared_content_source_type'),
      $container->get('request_stack'),
      $container->get('string_translation'),
      $container->get('messenger'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'shared_content_preview_form';
  }

  /**
   * Helper method so we can have consistent dialog options.
   *
   * @return string[]
   *   An array of jQuery UI elements to pass on to our dialog form.
   */
  protected static function getDataDialogOptions() {
    return [
      'width' => '80%',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Add the core AJAX library.
    $form['#attached']['library'][] = 'core/drupal.ajax';

    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['close'] = [
      '#type' => 'submit',
      '#value' => $this->t('Close'),
      '#ajax' => [
        'callback' => '::ajaxSubmitForm',
        'event' => 'click',
      ],
    ];
    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this
        ->t('Fetch to my site'),
      '#ajax' => [
        'callback' => '::ajaxSubmitForm',
        'event' => 'click',
      ],
      '#attributes' => ['class' => ['button--primary']],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Do nothing here. All actions are in ajaxSubmitForm.
  }

  /**
   * Implements the submit handler for the modal dialog AJAX call.
   *
   * @param array $form
   *   Render array representing from.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Array of AJAX commands to execute on submit of the modal form.
   */
  public function ajaxSubmitForm(array &$form, FormStateInterface $form_state) {

    // We begin building a new ajax response.
    $response = new AjaxResponse();

    if ($form_state->getTriggeringElement()['#value']->__toString() == 'Close') {
      $response->addCommand(new CloseModalDialogCommand());
      // @todo It would be better to reload the parent form with ajax.
      $response
        ->addCommand(new RedirectCommand($this->requestStack->getCurrentRequest()->server->get('HTTP_REFERER')));
      return $response;
    }
    else {

      // We don't want any messages that were added by submitForm().
      $this
        ->messenger()
        ->deleteAll();

      // Fetch the item using the arguments passed in $form_state.
      $this->fetchItem($form_state);

      // Redirect to the parent page.
      // @todo It would be better to reload the parent form with ajax.
      $response
        ->addCommand(new RedirectCommand($this->requestStack->getCurrentRequest()->server->get('HTTP_REFERER')));
    }

    // Finally return our response.
    return $response;
  }

  /**
   * Get item info from form and fetch it.
   */
  public function fetchItem(FormStateInterface $form_state) {
    $url = $form_state->getBuildInfo()['args'][0]['#server'];
    $uuid = $form_state->getBuildInfo()['args'][2];
    $type = $form_state->getBuildInfo()['args'][1];

    return $this->processItem($url, $uuid, $type);
  }

  /**
   * Processor for batch operations.
   */
  public function processItem($url, $uuid, $type) {
    $instance = $this->sharedSourceTypeManager->createInstance($type);
    $instance->saveFromSource($url, $uuid);
  }

}
