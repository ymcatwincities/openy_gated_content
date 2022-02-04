<?php

namespace Drupal\openy_gc_livechat\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the form for filter logs.
 */
class LogsSearchForm extends FormBase {

  /**
   * The currently active request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * LogsSearchForm constructor.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The currently active request object.
   */
  public function __construct(Request $request) {
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')->getCurrentRequest()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openy_gc_livechat__logs_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['filters'] = [
      '#type'  => 'fieldset',
      '#title' => $this->t('Filter'),
      '#open'  => TRUE,
    ];
    $form['filters']['title'] = [
      '#title' => 'Title',
      '#type' => 'search',
      '#default_value' => $this->request->query->get('title'),
    ];
    $form['filters']['start_from'] = [
      '#title' => 'Start From',
      '#type' => 'date',
      '#default_value' => $this->request->query->get('start_from'),
    ];
    $form['filters']['start_to'] = [
      '#title' => 'Start To',
      '#type' => 'date',
      '#default_value' => $this->request->query->get('start_to'),
    ];
    $form['filters']['actions'] = [
      '#type' => 'actions',
    ];
    $form['filters']['actions']['submit'] = [
      '#type'  => 'submit',
      '#value' => $this->t('Filter'),
    ];
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $url = Url::fromRoute('openy_gc_livechat.logs')
      ->setRouteParameters(
        [
          'title' => $form_state->getValue('title'),
          'start_from' => $form_state->getValue('start_from'),
          'start_to' => $form_state->getValue('start_to'),
        ],
    );
    $form_state->setRedirectUrl($url);
  }

}
