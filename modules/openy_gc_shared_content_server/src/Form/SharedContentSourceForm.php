<?php

namespace Drupal\openy_gc_shared_content_server\Form;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Shared content source edit forms.
 *
 * @ingroup openy_gc_shared_content_server
 */
class SharedContentSourceForm extends ContentEntityForm {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    /** @var \Drupal\openy_gc_shared_content_server\Entity\SharedContentSource $entity */
    $entity = $this->entity;

    // If the source api is not updated, check to see if it's capable.
    if (!empty($entity->getUrl()) && isset($form['api_updated']) &&
        $form['api_updated']['widget']['value']['#default_value'] !== TRUE) {
      $title = $form['api_updated']['widget']['#title']->__toString();
      $updated = $entity->isUpdated();

      // If the source isn't updated, don't allow the field to be modified.
      $status = new FormattableMarkup('<em class="color-warning">@status</em>', ['@status' => 'Nope.']);
      $form['api_updated']['#disabled'] = 'disabled';

      // If it is updated, yay!
      if ($updated) {
        $status = new FormattableMarkup(
          '<em class="color-success">@status</em>',
          ['@status' => 'New API Available!']
        );
        unset($form['api_updated']['#disabled']);
      }
      $form['api_updated']['widget']['value']['#title'] =
        $this->t('@title - @status', ['@title' => $title, '@status' => $status]);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Shared content source.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Shared content source.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.shared_content_source.canonical', ['shared_content_source' => $entity->id()]);
  }

}
