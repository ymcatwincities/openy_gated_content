<?php

namespace Drupal\openy_gc_auth\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Virtual Y login' block.
 *
 * @Block(
 *   id = "virtual_y_login",
 *   admin_label = @Translation("Virtual Y Login"),
 *   category = @Translation("Virtual Y")
 * )
 */
class VirtualYLoginBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The configuration factory.
   *
   * @var ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Current user service instance.
   *
   * @var AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    AccountProxyInterface $current_user
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
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
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $virtual_y_config = $this->configFactory->get('openy_gc_auth.settings');
    $active_provider = $virtual_y_config->get('active_provider');
    $identityProviderManager = \Drupal::service('plugin.manager.gc_identity_provider');
    $plugin_definition = $identityProviderManager->getDefinition($virtual_y_config->get('active_provider'), FALSE);
    if (!$plugin_definition) {
      return [
        '#markup' => 'Error: Auth plugin is not found'
      ];
    }
    $plugin_instance = $identityProviderManager->createInstance($active_provider);
    $form = $plugin_instance->getLoginForm();

    // For some providers e.g. Daxko, Personify we do not display form but redirect to login immediately.
    if ($form instanceof RedirectResponse) {
      return $form->send();
    }

    return [
      [
        '#markup' => '<h1>Test</h1>'
      ],
      $form
    ];
  }

}
