<?php
require_once 'vendor/autoload.php';

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ComponentInterface;
use Ratchet\ConnectionInterface;

class NotificationsServer implements MessageComponentInterface {
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "Nova conexão! - ({$conn->resourceId})";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Coneão {$conn->resourceId} desconectou-se";
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        echo "Ocorreu um erro: {$e->getMessage()}\n";
        $conn->close();
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new NotificationsServer()
        )
    ),
    8080
);

echo "O servirodr WebSocket foi iniciado na porta 8080";
$server->run();

?>