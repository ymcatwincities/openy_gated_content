<?php

namespace Drupal\openy_gc_demo\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate_plus\Plugin\migrate\process\EntityGenerate;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Process plugin that creates paragraphs on fly.
 *
 * @MigrateProcessPlugin(
 *   id = "fly_paragraph"
 * )
 */
class FlyParagraph extends EntityGenerate {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrateExecutable, Row $row, $destinationProperty) {

    if (empty($value) && (!is_array($value))) {
      return NULL;
    }

    $entity_arr = [
      'type' => $value['bundle'],
    ];

    if (!empty($value['values'])) {
      $entity_arr += $value['values'];
    }

    $p = Paragraph::create($entity_arr);
    $p->isNew();
    $p->save();

    return [
      'target_id' => $p->id(),
      'target_revision_id' => $p->getRevisionId(),
    ];
  }

}
