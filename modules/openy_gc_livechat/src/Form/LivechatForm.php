<?php

namespace Drupal\openy_gc_livechat\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class LivechatForm.
 *
 * @package Drupal\openy_gc_livechat\Form
 */
class LivechatForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'livechat_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['chat_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::messenger()->addMessage($this->t('Your message was submitted!'));
  }

}
