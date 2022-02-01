<?php

namespace Drupal\openy_gc_livechat\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Livechat Block' block.
 *
 * @Block(
 *   id = "openy_gc_livechat_block",
 *   admin_label = @Translation("Livechat Block"),
 *   category = @Translation("Virtual Y")
 * )
 */
class LivechatBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * ConfigFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    FormBuilderInterface $form_builder
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('form_builder'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $settings = $this->configFactory->get('openy_gc_livechat.settings');
    return [
      '#title' => 'Chat block',
      '#theme' => 'livechat_block',
      '#form' => $this->formBuilder->getForm('\Drupal\openy_gc_livechat\Form\LivechatForm'),
      '#attached' => [
        'library' => [
          'openy_gc_livechat/chat',
        ],
        'drupalSettings' => [
          'openy_gc_livechat' => [
            'port' => $settings->get('port'),
            'mode' => $settings->get('mode'),
          ],
        ],
      ],
    ];
  }

}
