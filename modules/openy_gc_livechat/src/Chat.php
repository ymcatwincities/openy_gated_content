<?php

namespace Drupal\openy_gc_livechat;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * Provides Chat class based on Ratchet component.
 */
class Chat implements MessageComponentInterface {

  /**
   * Clients connected to websocket server.
   *
   * @var \SplObjectStorage
   */
  protected $clients;

  /**
   * Chat constructor.
   */
  public function __construct() {
    $this->clients = new \SplObjectStorage();
  }

  /**
   * {@inheritdoc}
   */
  public function onOpen(ConnectionInterface $conn) {
    // Set request's path as chatroom's ID.
    $path = $conn->httpRequest->getUri()->getPath();
    $path = str_replace('/', '', $path);
    $info['chatroom_id'] = $path;
    $this->clients->attach($conn, $info);

    // Get chat history.
    $history = $this->loadHistory($path);
    $data = [
      'count' => count($this->clients),
      'message_type' => 'history',
      'history' => $history,
    ];
    foreach ($this->clients as $client) {
      // Send chat history only for newly connected user.
      if ($client->resourceId == $conn->resourceId) {
        $client->send(json_encode($data));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onMessage(ConnectionInterface $from, $msg) {
    $data = json_decode($msg, TRUE);

    if (
      (isset($data['disableChat']) && $data['disableChat']) ||
      (isset($data['enableChat']) && $data['enableChat'])
    ) {
      // Check if request sent by instructor role only.
      $user_storage = \Drupal::service('entity_type.manager')->getStorage('user');
      $user = $user_storage->load($data['uid']);
      $user_roles = $user->getRoles();

      if (in_array('virtual_trainer', $user_roles)) {
        $this->clients->rewind();

        $disabledVirtualChatrooms = \Drupal::state()->get('disabledVirtualChatrooms', []);

        if (isset($data['enableChat']) && $data['enableChat']) {
          $data['message_type'] = 'enableChat';
          unset($disabledVirtualChatrooms[$data['chatroom_id']]);
          \Drupal::state()->set('disabledVirtualChatrooms', $disabledVirtualChatrooms);
        }

        if (isset($data['disableChat']) && $data['disableChat']) {
          $data['message_type'] = 'disableChat';
          // Delete history of the chat.
          $db = \Drupal::database();
          $db->delete('openy_gc_livechat__chat_history')
            ->condition('cid', $data['chatroom_id'])
            ->execute();

          $disabledVirtualChatrooms[$data['chatroom_id']] = $data['chatroom_id'];
          \Drupal::state()->set('disabledVirtualChatrooms', $disabledVirtualChatrooms);
        }

        while ($this->clients->valid()) {
          $client = $this->clients->current();
          $info = $this->clients->getInfo();
          // Disable chat only for clients connected to the same chatroom.
          if ($info['chatroom_id'] == $data['chatroom_id']) {
            $client->send(json_encode($data));
          }
          $this->clients->next();
        }
      }
    }

    $db = \Drupal::database();
    $db->insert('openy_gc_livechat__chat_history')
      ->fields([
        'cid',
        'title',
        'start',
        'uid',
        'username',
        'message',
        'created',
      ])
      ->values([
        'cid' => $data['chatroom_id'],
        'title' => $data['title'],
        'start' => $data['start'],
        'uid' => $data['uid'],
        'username' => $data['username'],
        'message' => $data['message'],
        'created' => time(),
      ])
      ->execute();

    $this->clients->rewind();
    $data['count'] = count($this->clients);
    while ($this->clients->valid()) {
      $client = $this->clients->current();
      $info   = $this->clients->getInfo();
      if ($from == $client) {
        $data['from'] = 'Me';
      }
      else {
        $data['from'] = $data['username'];
      }
      // Send message only to clients connected to the same chatroom.
      if ($info['chatroom_id'] == $data['chatroom_id']) {
        $client->send(json_encode($data));
      }

      $this->clients->next();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onClose(ConnectionInterface $conn) {
    $this->clients->detach($conn);
  }

  /**
   * {@inheritdoc}
   */
  public function onError(ConnectionInterface $conn, \Exception $e) {
    $conn->close();
  }

  /**
   * Method for retrieving chat history by passed chat id.
   */
  private function loadHistory($cid) {
    return \Drupal::database()->select('openy_gc_livechat__chat_history', 'ch')
      ->fields('ch')
      ->condition('cid', $cid)
      ->orderBy('ch.created')
      ->execute()
      ->fetchAll();
  }

}
