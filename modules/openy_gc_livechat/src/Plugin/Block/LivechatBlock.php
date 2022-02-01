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
    $settings = \Drupal::service('config.factory')->get('openy_gc_livechat.settings');
    return [
      '#title' => 'Chat block',
      '#theme' => 'livechat_block',
      '#form' => \Drupal::formBuilder()->getForm('\Drupal\openy_gc_livechat\Form\LivechatForm'),
      '#attached' => [
        'library' => [
          'openy_gc_livechat/chat',
        ],
        'drupalSettings' => [
          'openy_gc_livechat' => [
            'port' => $settings->get('port'),
            'mode' => $settings->get('mode'),
          ]
        ],
      ],
    ];
  }

}
