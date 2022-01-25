<?php

namespace Drupal\openy_gc_livechat;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

/**
 * Class SocketServer.
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
      8081
    );

    $server->run();

  }

}
