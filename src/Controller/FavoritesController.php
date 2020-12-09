<?php

namespace Drupal\openy_gated_content\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * Class Favorites Controller.
 */
class FavoritesController extends ControllerBase {

  /**
   * The current active database's master connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The serializer.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * Constructs a CustomFormattersController object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The current active database's master connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
    $this->serializer = new Serializer([], [new JsonEncoder()]);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * List of favorites items for current user.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json Array with favorites items grouped by entity type and bundle.
   */
  public function list() {
    $data = [
      'node' => [
        'vy_blog_post' => [],
        'gc_video' => [],
      ],
      'eventinstance' => [
        'live_stream' => [],
        'virtual_meeting' => [],
      ],
      'taxonomy_term' => [
        'gc_category' => [],
      ],
    ];

    $uid = $this->currentUser()->id();
    $query = $this->database->select('vy_favorite_item', 'f');
    $query->condition('f.uid', $uid);
    $query->fields('f', [
      'id',
      'ref_entity_type',
      'ref_entity_bundle',
      'ref_entity_id',
    ]);
    $result = $query->execute()->fetchAll();
    if (empty($result)) {
      return new JsonResponse($data);
    }

    foreach ($result as $item) {
      $data[$item->ref_entity_type][$item->ref_entity_bundle][] = [
        'id' => $item->id,
        'entity_id' => $item->ref_entity_id,
      ];
    }

    return new JsonResponse($data);
  }

  /**
   * Add item to Favorites list.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json Array with favorites items grouped by entity type and bundle.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function add(Request $request) {
    $data = $this->getData($request);
    if ($data instanceof JsonResponse) {
      return $data;
    }

    $favorite_item = $this->entityTypeManager()
      ->getStorage('vy_favorite_item')
      ->create([
        'ref_entity_type' => $data['entity_type'],
        'ref_entity_bundle' => $data['entity_bundle'],
        'ref_entity_id' => $data['entity_id'],
      ]);
    $favorite_item->save();
    // Return full list of favorites.
    return $this->list();
  }

  /**
   * Remove item from the Favorites list.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json Array with favorites items grouped by entity type and bundle.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function delete(Request $request) {
    $data = $this->getData($request);
    if ($data instanceof JsonResponse) {
      return $data;
    }
    $favorite_items = $this->entityTypeManager()
      ->getStorage('vy_favorite_item')
      ->loadByProperties([
        'uid' => $this->currentUser()->id(),
        'ref_entity_type' => $data['entity_type'],
        'ref_entity_bundle' => $data['entity_bundle'],
        'ref_entity_id' => $data['entity_id'],
      ]);
    if (empty($favorite_items)) {
      return new JsonResponse(['message' => 'Item not exists']);
    }
    foreach ($favorite_items as $item) {
      $item->delete();
    }

    // Return full list of favorites.
    return $this->list();
  }

  /**
   * Helper function for valid data extracting.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json Array with favorites items grouped by entity type and bundle.
   */
  protected function getData(Request $request) {
    $data = $this->serializer->decode($request->getContent(), $request->getRequestFormat());
    if (!$data) {
      return new JsonResponse(['message' => 'Missed request content.'], 400);
    }
    $required_data = ['entity_bundle', 'entity_id', 'entity_type'];
    foreach ($required_data as $property) {
      if (!isset($data[$property]) || !$data[$property]) {
        return new JsonResponse(['message' => "Missed required property '$property'."], 400);
      }
    }

    return $data;
  }

}
