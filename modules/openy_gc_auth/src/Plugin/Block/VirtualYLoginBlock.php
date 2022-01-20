<?php

namespace Drupal\openy_gc_auth\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\openy_gc_auth\GCIdentityProviderManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
    $plugin_instance = $this->identityProviderManager->createInstance($active_provider);

    $form = $plugin_instance->getLoginForm();

    // For some providers e.g. Daxko, Personify we do not display form
    // but redirect to login immediately.
    // phpcs:disable
    if ($form instanceof RedirectResponse) {
      return [
        '#cache' => [
          'max-age' => 0,
        ],
        $form->send(),
      ];
    }
    // phpcs:enable

    $form['#attached']['library'][] = 'openy_gc_auth/auth_destination';
    return $form;
  }

}
