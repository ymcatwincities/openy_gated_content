<?php

namespace Drupal\openy_gc_livechat\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provide actions to update current user name.
 */
class LiveChatController extends ControllerBase {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * LiveChatController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * Get current user name.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JsonResponse.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getLiveChatData() {
    $user_storage = $this->entityTypeManager()->getStorage('user');
    $user = $user_storage->load($this->currentUser()->id());
    $ratchet_settings = $this->configFactory->get('openy_gc_livechat.settings');

    $data = [
      'name' => $user->getAccountName(),
      'user_id' => $this->currentUser()->id(),
      'ratchet' => [
        'port' => $ratchet_settings->get('port'),
        'mode' => $ratchet_settings->get('mode'),
      ],
    ];

    return new JsonResponse($data, 200);
  }

  /**
   * Update current user name.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JsonResponse.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function updateName(Request $request) {
    $params = json_decode($request->getContent(), TRUE);
    if (!isset($params['name'])) {
      return new JsonResponse(['message' => 'Argument name missed'], 400);
    }
    $user_storage = $this->entityTypeManager()->getStorage('user');
    $user = $user_storage->load($this->currentUser()->id());

    if ($user->getAccountName() == $params['name']) {
      return new JsonResponse(['message' => 'There no updates in user name'], 200);
    }

    try {
      $user->setUsername($params['name']);
      $user->save();
    }
    catch (\Exception $exception) {
      return new JsonResponse(['message' => 'User with that username already exists'], 400);
    }

    return new JsonResponse(['message' => 'Username successfully updated'], 200);
  }

}
