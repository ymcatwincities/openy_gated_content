<?php

namespace Drupal\openy_gc_personal_training\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\openy_gc_auth\GCIdentityProviderManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides a 'Virtual Y PT' block.
 *
 * @Block(
 *   id = "virtual_y_personal_training",
 *   admin_label = @Translation("Virtual Y Personal Training"),
 *   category = @Translation("Virtual Y")
 * )
 */
class VirtualYPersonalTrainingBlock extends BlockBase {

  /**
   * ConfigFactory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * GC Identity Provider Manager.
   *
   * @var \Drupal\openy_gc_auth\GCIdentityProviderManager
   */
  protected $identityProviderManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    AccountProxyInterface $current_user,
    GCIdentityProviderManager $identityProviderManager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
    $this->identityProviderManager = $identityProviderManager;
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
      $container->get('current_user'),
      $container->get('plugin.manager.gc_identity_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $virtual_y_config = $this->configFactory->get('openy_gc_auth.settings');
    $active_provider = $virtual_y_config->get('active_provider');
    $plugin_definition = $this->identityProviderManager->getDefinition($virtual_y_config->get('active_provider'), FALSE);
    if (!$plugin_definition) {
      return [
        '#markup' => 'Error: Auth plugin is not found',
      ];
    }

    return ['#markup' => 'test'];
  }
}