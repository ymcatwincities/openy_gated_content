<?php

namespace Drupal\openy_gc_livechat;

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Socket\SecureServer;
use React\Socket\SocketServer;

/**
 * Provides SocketServer class for running websocket server.
 *
 * @package Drupal\openy_gc_livechat
 */
class GCSocketServer {

  /**
   * Starts websocket server.
   */
  public static function run() {
    $settings = \Drupal::service('config.factory')->get('openy_gc_livechat.settings');
    if ($settings->get('mode') == 'https') {
      $loop = Loop::get();
      $webSock = new SecureServer(
        new SocketServer('0.0.0.0:' . $settings->get('port'), [], $loop),
        $loop,
        [
          'local_cert' => $settings->get('cert_path'),
          'local_pk' => $settings->get('key_path'),
          'allow_self_signed' => !$settings->get('env') == 'local',
          'verify_peer' => FALSE,
        ]
      );
      $server = new IoServer(
        new HttpServer(
          new WsServer(
            new Chat()
          )
        ),
        $webSock
      );
      $loop->run();
    }
    else {
      // Default http mode.
      $server = IoServer::factory(
        new HttpServer(
          new WsServer(
            new Chat()
          )
        ),
        $settings->get('port'),
      );

      $server->run();
    }

  }

}
