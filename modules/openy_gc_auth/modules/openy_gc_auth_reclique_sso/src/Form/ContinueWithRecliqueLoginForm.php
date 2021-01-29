<?php

namespace Drupal\openy_gc_auth_reclique_sso\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Build button which provides authentication through Reclique CRM.
 *
 * @package Drupal\openy_gc_auth_reclique_sso\Form
 */
class ContinueWithRecliqueLoginForm extends FormBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openy_gc_auth_reclique_sso_login_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['submit'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute('openy_gc_auth_reclique_sso.authenticate_redirect'),
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
    $form_state->setRedirectUrl(Url::fromRoute('openy_gc_auth_reclique_sso.authenticate_redirect'));
  }

}
