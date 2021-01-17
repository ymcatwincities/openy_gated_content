<?php

namespace Drupal\openy_gc_auth;

use Drupal\node\NodeInterface;
use Drupal\Core\Database\Database;

/**
 * Class GCAuthManager for common methods.
 */
class GCAuthManager {

  /**
   * Check if provided paragraph exists on the node.
   */
  public function checkIfParagraphAtNode(NodeInterface $node, $paragraph_id) {
    $connection = Database::getConnection();

    $result = $connection->select('paragraphs_item_field_data', 'pd')
      ->fields('pd', ['id'])
      ->condition('pd.parent_id', $node->id())
      ->condition('pd.type', $paragraph_id)
      ->countQuery()
      ->execute()
      ->fetchCol();
    return reset($result);
  }

}
