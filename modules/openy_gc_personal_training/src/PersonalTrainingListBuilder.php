<?php

namespace Drupal\openy_gc_personal_training;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of Personal training entities.
 *
 * @ingroup openy_gc_personal_training
 */
class PersonalTrainingListBuilder extends EntityListBuilder {

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('config.factory')
    );
  }

  /**
   * Constructs a new EntityListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Config factory class.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, ConfigFactoryInterface $config) {
    parent::__construct($entity_type, $storage, $config);
    $this->configFactory = $config;

  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    $header['customer'] = $this->t('Customer');
    $header['instructor'] = $this->t('Instructor');
    $header['training_type'] = $this->t('Training type');
    $header['date'] = $this->t('Date');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $virtualy_config = $this->configFactory
      ->get('openy_gated_content.settings');

    $url = Url::fromUserInput(
      $virtualy_config->get('virtual_y_url')
      . '#/personal-training/'
      . $entity->uuid()
    );

    $row['id'] = Link::fromTextAndUrl(
      '#' . $entity->id(),
      $url
    );

    $row['title'] = Link::createFromRoute(
      $entity->label(),
      'entity.personal_training.canonical',
      ['personal_training' => $entity->id()]

    );

    $row['customer'] = Link::createFromRoute(
      $entity->customer_id->entity->label(),
      'entity.user.canonical',
      ['user' => $entity->customer_id->entity->id()]
    );

    $row['instructor'] = Link::createFromRoute(
      $entity->instructor_id->entity->label(),
      'entity.user.canonical',
      ['user' => $entity->instructor_id->entity->id()]
    );

    $row['training_type'] = $entity->get('training_type')->value;
    $row['date'] = $entity->get('date')->value;

    return $row + parent::buildRow($entity);
  }

}
