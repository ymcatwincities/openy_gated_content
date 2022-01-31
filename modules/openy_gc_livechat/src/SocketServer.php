<?php

namespace Drupal\openy_gc_livechat;

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

/**
 * Provides SocketServer class for running websocket server.
 *
 * @package Drupal\openy_gc_livechat
 */
class SocketServer {

  /**
   * Starts websocket server.
   */
  public static function run() {
    $server = IoServer::factory(
      new HttpServer(
        new WsServer(
          new Chat()
        )
      ),
      \Drupal::service('config.factory')->get('openy_gc_livechat.settings')->get('port'),
    );

    $server->run();

  }

}
