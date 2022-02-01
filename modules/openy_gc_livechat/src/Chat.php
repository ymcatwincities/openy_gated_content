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

    // Get chat history and pass to all clients connected to this chat.
    $history = $this->loadHistory($path);
    if ($history) {
      $data['message_type'] = 'history';
      $data['history'] = $history;
      foreach ($this->clients as $client) {
        // Send chat history only for newly connected user.
        if ($client->resourceId == $conn->resourceId) {
          $client->send(json_encode($data));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onMessage(ConnectionInterface $from, $msg) {
    $data = json_decode($msg, TRUE);

    $db = \Drupal::database();
    $db->insert('openy_gc_livechat__chat_history')
      ->fields([
        'cid',
        'uid',
        'username',
        'message',
        'created',
      ])
      ->values([
        'cid' => $data['chatroom_id'],
        'uid' => $data['uid'],
        'username' => $data['username'],
        'message' => $data['message'],
        'created' => time(),
      ])
      ->execute();

    $this->clients->rewind();
    while ($this->clients->valid()) {
      $client = $this->clients->current();
      $info   = $this->clients->getInfo();
      if ($from == $client) {
        $data['from'] = 'Me';
      }
      else {
        // @todo: Pass username from client side.
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
