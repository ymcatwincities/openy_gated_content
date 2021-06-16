<?php

namespace Drupal\openy_gc_personal_training;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class for reacting to Personal Trainings events.
 */
class PersonalTrainingSeriesOperations implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Personal Trainings Series Manager service.
   *
   * @var \Drupal\openy_gc_personal_training\PersonalTrainingSeriesManagerInterface
   */
  protected $personalTrainingSeriesManager;

  /**
   * PersonalTrainingSeriesOperations constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\openy_gc_personal_training\PersonalTrainingSeriesManagerInterface $personal_training_series_manager
   *   Livestream Manager service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    PersonalTrainingSeriesManagerInterface $personal_training_series_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->personalTrainingSeriesManager = $personal_training_series_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('personal_training_series.manager')
    );
  }

  /**
   * Implements logic for the ENTITY_TYPE_insert hook.
   *
   * @param \Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface $training_series
   *   Entity that was just created.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @see hook_node_insert()
   */
  public function entityInsert(PersonalTrainingInterface $training_series): void {
    // Generate personal_training instances for a new training_series.
    $this->personalTrainingSeriesManager->buildBatch($training_series->id(), ['generateItemsForSeries']);
  }

  /**
   * Implements logic for the ENTITY_TYPE_update hook.
   *
   * @param \Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface $training_series
   *   Entity that was just saved.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   *
   * @see hook_node_update()
   */
  public function entityUpdate(PersonalTrainingInterface $training_series) {
    // Rebuild all series instances if series was changed.
    $this->personalTrainingSeriesManager->buildBatch($training_series->id(), [
      'deleteItemsOfSeries',
      'generateItemsForSeries',
    ]);
  }

  /**
   * Implements logic for the ENTITY_TYPE_delete hook.
   *
   * @param \Drupal\openy_gc_personal_training\Entity\PersonalTrainingInterface $training_series
   *   Node object that was just deleted.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @see hook_node_delete()
   */
  public function entityDelete(PersonalTrainingInterface $training_series) {
    // Delete personal_training instances related to training_series.
    $this->personalTrainingSeriesManager->buildBatch($training_series->id(), ['deleteItemsOfSeries']);
  }

}
