<?php

namespace Drupal\openy_gated_content\Form;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Rebuild Durations Form class.
 */
class RebuildDurationsForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'virtual_y_rebuild_durations';
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('<em>Duration reference</em> field of every <em>Virtual Y Video</em> node is going to be populated based on the value of <em>Duration</em> field. If <em>Duration</em> field is empty, default Duration term will be used.');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('entity.taxonomy_vocabulary.overview_form', ['taxonomy_vocabulary' => 'gc_duration']);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to rebuild all the references to <em>Duration</em> taxonomies within every <em>Virtual Y Video</em> node?');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $batch_builder = new BatchBuilder();
    $batch_builder
      ->setFile(drupal_get_path('module', 'openy_gc_storage') . '/openy_gc_storage.post_update.php')
      ->addOperation('_openy_gc_storage_build_duration_references')
      ->setFinishCallback([$this, 'finished']);

    batch_set($batch_builder->toArray());
  }

  /**
   * Finished callback for batch.
   */
  public function finished($success, $results, $operations) {
    $message = $this->t('Number of video nodes affected by batch: @count', [
      '@count' => $results['processed'],
    ]);

    $this->messenger()->addStatus($message);

    return new RedirectResponse($this->getCancelUrl()->toString());
  }

}
