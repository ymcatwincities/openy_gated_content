<?php

namespace Drupal\openy_gc_livechat\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides the form for filter logs.
 */
class LogsSearchForm extends FormBase {

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
      '#open'  => true,
    ];
    $form['filters']['title'] = [
      '#title' => 'Title',
      '#type' => 'search',
      '#default_value' => \Drupal::request()->query->get('title'),
    ];
    $form['filters']['start_from'] = [
      '#title' => 'Start From',
      '#type' => 'date',
      '#default_value' => \Drupal::request()->query->get('start_from'),
    ];
    $form['filters']['start_to'] = [
      '#title' => 'Start To',
      '#type' => 'date',
      '#default_value' => \Drupal::request()->query->get('start_to'),
    ];
    $form['filters']['actions'] = [
      '#type' => 'actions'
    ];
    $form['filters']['actions']['submit'] = [
      '#type'  => 'submit',
      '#value' => $this->t('Filter')
    ];
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array & $form, FormStateInterface $form_state) {
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
