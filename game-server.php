<?php
    use Ratchet\Server\IoServer;
    use Ratchet\Http\HttpServer;
    use Ratchet\WebSocket\WsServer;
    use Rafa\Socket\Game;

    require 'vendor/autoload.php';

        $game = new Game();

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $game
                )
            ),
            8081
        );

        $server->run();

        // $server->loop->stop();