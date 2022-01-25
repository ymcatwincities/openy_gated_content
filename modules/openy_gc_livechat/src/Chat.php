<?php

namespace Drupal\openy_gc_livechat;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

/**
 * Chat class.
 */
class Chat implements MessageComponentInterface {

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
    $info['chatroom_id'] = $path;
    $this->clients->attach($conn, $info);
  }

  /**
   * {@inheritdoc}
   */
  public function onMessage(ConnectionInterface $from, $msg) {
    $data = json_decode($msg, true);

    $db = \Drupal::database();
    $db->insert('openy_gc_livechat__chat_history')
      ->fields([
        'cid',
        'uid',
        'username',
        'message',
        'created'
      ])
      ->values([
        'cid' => $data['chatroom_id'],
        'uid' => 'test_uid', // @todo: pass from client side.
        'username' => 'test_username', // @todo: pass from client side.
        'message' => $data['msg'],
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
        $data['from'] = '$user_name'; // @todo: pass from client side.
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

}
