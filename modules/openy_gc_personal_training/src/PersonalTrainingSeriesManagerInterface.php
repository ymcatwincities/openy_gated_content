<?php

namespace Drupal\openy_gc_personal_training;

/**
 * Provides an interface for Personal Trainings Series manager.
 */
interface PersonalTrainingSeriesManagerInterface {

  /**
   * Generate Series items callback for a batch.
   *
   * @param int $series_id
   *   Personal Trainings Series ID.
   * @param array $context
   *   Context array, changing during processing.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function generateItemsForSeries(int $series_id, array &$context): void;

  /**
   * Update Series items' data callback for a batch.
   *
   * @param int $series_id
   *   Personal Trainings Series ID.
   * @param array $context
   *   Context array, changing during processing.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateItemsOfSeries(int $series_id, array &$context): void;

  /**
   * Delete Series items callback for a batch.
   *
   * @param int $series_id
   *   Personal Trainings Series ID.
   * @param array $context
   *   Context array, changing during processing.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deleteItemsOfSeries(int $series_id, array &$context): void;

  /**
   * Create the batch and run a processing in it.
   *
   * @param int $id
   *   Entity, which will be sent as a first param to a service method.
   * @param array $methods
   *   Operation methods, that will be used in batch processing.
   */
  public function buildBatch(int $id, array $methods): void;

  /**
   * Finished callback for batch.
   */
  public function batchFinished($success, $results, $operations): void;

}
