<?php

namespace Drupal\openy_gc_livechat\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Livechat Block' block.
 *
 * @Block(
 *   id = "openy_gc_livechat_block",
 *   admin_label = @Translation("Livechat Block"),
 *   category = @Translation("Virtual Y")
 * )
 */
class LivechatBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#title' => 'Chat block',
      '#theme' => 'chat_block',
      '#form' => \Drupal::formBuilder()->getForm('\Drupal\openy_gc_livechat\Form\LivechatForm'),
      '#attached' => [
        'library' => [
          'openy_gc_livechat/chat',
        ],
      ],
    ];
  }

}
