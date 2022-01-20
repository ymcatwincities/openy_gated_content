<?php

namespace Drupal\openy_gated_content\Controller;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\openy_gated_content\VirtualYAccessTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for Virtual Y Base routes.
 */
class EventsController extends ControllerBase {

  use VirtualYAccessTrait;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The controller constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(
    Connection $connection,
    ModuleHandlerInterface $module_handler
  ) {
    $this->connection = $connection;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('module_handler')
    );
  }

  /**
   * Events list with filters by content type/bundle.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json Array with entities' uuids.
   */
  public function list(Request $request): JsonResponse {
    $request_data = UrlHelper::parse($request->getUri());

    $query = $request_data['query'];
    foreach (['type', 'start_date', 'end_date'] as $param) {
      if (empty($query) || !isset($query[$param])) {
        return new JsonResponse(['message' => 'Missed "' . $param . '" query param.'], 400);
      }
    }

    if ($query['type'] !== 'all' && !isset($query['bundle'])) {
      return new JsonResponse(['message' => 'For specified "type" should be "bundle" query param.'], 400);
    }

    $results = [];

    switch ($query['type']) {
      case 'all':
        $results = $this->getEventInstances(
          [
            'live_stream',
            'virtual_meeting',
          ],
          new DrupalDateTime($query['start_date']),
          new DrupalDateTime($query['end_date'])
        );
        break;

      case 'eventinstance':
        $results = $this->getEventInstances(
          [$query['bundle']],
          new DrupalDateTime($query['start_date']),
          new DrupalDateTime($query['end_date'])
        );
        break;
    }

    $this->moduleHandler->alter(
      'openy_gated_content_list_events',
      $results,
      $query
    );

    return new JsonResponse($results);
  }

  /**
   * Lists eventinstance entities, filtered by bundle(s).
   *
   * @param array $bundles
   *   Event instance bundles for results filtering.
   * @param \Drupal\Core\Datetime\DrupalDateTime $start_date
   *   Start date for results filtering.
   * @param \Drupal\Core\Datetime\DrupalDateTime $end_date
   *   End date for results filtering.
   *
   * @return array
   *   Array with entities' uuids.
   */
  protected function getEventInstances(
    array $bundles,
    DrupalDateTime $start_date = NULL,
    DrupalDateTime $end_date = NULL
  ): array {
    $roles = $this->currentUser()->getRoles(TRUE);
    $vy_roles = array_filter($roles, function ($role) {
      return strpos($role, 'virtual_y_') !== FALSE || $role === 'virtual_y';
    });
    $query = $this->connection->select('eventinstance', 'ei');
    $query->leftJoin('eventinstance_field_data', 'eifd', 'ei.id = eifd.id');
    $query->leftJoin('eventinstance__field_ls_title', 'eit', 'eit.entity_id = ei.id');
    $query->leftJoin('eventinstance__field_ls_host_name', 'eih', 'eih.entity_id = ei.id');
    $query->condition('eifd.type', $bundles, 'IN');
    $query->condition('eifd.status', 1);
    $query->leftJoin('eventseries_field_data', 'esfd', 'eifd.eventseries_id = esfd.id');
    $query->leftJoin('eventseries__field_ls_host_name', 'esh', 'esh.entity_id = esfd.id');

    if (!empty($vy_roles)) {
      $eifd_or_group = $query->orConditionGroup();
      $esfd_or_group = $query->orConditionGroup();
      foreach ($vy_roles as $role) {
        $eifd_or_group->condition('eifd.field_vy_permission', $role, '=');
        $eifd_or_group->condition('eifd.field_vy_permission', '%' . $role . ',%', 'LIKE');
        $eifd_or_group->condition('eifd.field_vy_permission', '%,' . $role, 'LIKE');
        $esfd_or_group->condition('esfd.field_vy_permission', $role, '=');
        $esfd_or_group->condition('esfd.field_vy_permission', '%' . $role . ',%', 'LIKE');
        $esfd_or_group->condition('esfd.field_vy_permission', '%,' . $role, 'LIKE');
      }
      $query->condition($query->orConditionGroup()
        ->condition($eifd_or_group)
        ->condition($query->andConditionGroup()
          ->isNull('eifd.field_vy_permission')
          ->condition($esfd_or_group)
        )
      );
    }

    if (isset($start_date)) {
      $query->condition('eifd.date__end_value', $start_date->format('c'), '>=');
    }

    if (isset($end_date)) {
      $query->condition('eifd.date__value', $end_date->format('c'), '<=');
    }

    $query->fields('ei', ['id', 'type', 'uuid']);
    $query->fields('eit', ['field_ls_title_value']);
    $query->fields('eih', ['field_ls_host_name_value']);
    $query->fields('eifd', ['date__value', 'date__end_value']);
    $query->fields('esfd', ['title']);
    $query->fields('esh', ['field_ls_host_name_value']);
    $query->orderBy('eifd.date__value');
    $query->distinct(TRUE);

    $result = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

    $instances = [];
    $timezone = new \DateTimeZone('UTC');
    foreach ($result as $item) {
      $instances[] = [
        'type' => 'eventinstance',
        'bundle' => $item['type'],
        'id' => $item['id'],
        'uuid' => $item['uuid'],
        'title' => $item['field_ls_title_value'] ?: $item['title'],
        'host_name' => $item['field_ls_host_name_value'] ?: $item['esh_field_ls_host_name_value'],
        'date' => [
          'value' => (new DrupalDateTime($item['date__value'], $timezone))->format('c'),
          'end_value' => (new DrupalDateTime($item['date__end_value'], $timezone))->format('c'),
        ],
      ];
    }
    return $instances;
  }

}
