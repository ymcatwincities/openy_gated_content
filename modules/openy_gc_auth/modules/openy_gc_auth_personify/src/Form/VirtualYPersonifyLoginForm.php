<?php

namespace Drupal\openy_gc_auth_personify\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class VirtualYPersonifyLoginForm Form.
 *
 * @package Drupal\openy_gc_auth_personify\Form
 */
class VirtualYPersonifyLoginForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openy_gc_auth_personify_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['submit'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute('openy_gc_auth_personify.personify_check'),
      '#title' => $this->t('Enter Virtual Y'),
      '#attributes' => [
        'class' => [
          'gc-button',
        ],
      ],
    ];

    $form['#attributes'] = [
      'class' => 'text-center',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirectUrl(Url::fromRoute('openy_gc_auth_personify.personify_check'));
  }

}
