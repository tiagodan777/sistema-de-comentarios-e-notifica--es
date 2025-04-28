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
    private $pdo;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;

        $this->pdo = new \PDO("mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;host=localhost;dbname=notificacoes", "root", "root");
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "Nova conexão! - ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);

        $data['status'] = 0;

        var_dump($data);

        echo "YOU PARVA\n";

        if (isset($data['type']) && $data['type'] === 'new_comment') {
            $statement = $this->pdo->prepare("INSERT INTO comments (comment_subject, comment_text, comment_status) VALUES (:comment_subject, :comment_text, :comment_status)");
            $statement->execute(['comment_subject' => $data['subject'], 'comment_text' => $data['comment'], 'comment_status' => $data['status']]);
            $this->broadcastNotifications();
            echo "OKI DOKI PARVA\n";
        } else {
            echo "Sua parva\n";
        }
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

    private function broadcastNotifications() {
        foreach ($this->clients as $client) {
            // Fazer a seguir
        }
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