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
        $this->sendNotifications($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);

        $data['status'] = 0;

        if (isset($data['type']) && $data['type'] === 'new_comment') {
            $statement = $this->pdo->prepare("INSERT INTO comments (comment_subject, comment_text, comment_status) VALUES (:comment_subject, :comment_text, :comment_status)");
            $statement->execute(['comment_subject' => $data['subject'], 'comment_text' => $data['comment'], 'comment_status' => $data['status']]);
            $this->broadcastNotifications();
        }

        if (isset($data['type']) && $data['type'] === 'mark_as_read') {
            $this->markNofiticationAsRead($data['comment_id']);
            $this->broadcastNotifications();
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
            $this->sendNotifications($client);
        }
    }

    private function markNofiticationAsRead($commentId) {
        $statement = $this->pdo->prepare("UPDATE comments SET comment_status = 1 WHERE comment_id = :comment_id");
        $statement->execute(['comment_id' => $commentId]);
    }

    private function sendNotifications($conn) {
        $statement = $this->pdo->query("SELECT * FROM comments ORDER BY comment_id DESC LIMIT 5;");
        $notifications = $statement->fetchAll(PDO::FETCH_ASSOC);

        $statement = $this->pdo->query("SELECT COUNT(*) as unread_count FROM comments WHERE comment_status = 0");

        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $unreadCount = $row['unread_count'];

        $response = [
            'type' => 'notification',
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ];

        $conn->send(json_encode($response));
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

echo "O servidor WebSocket foi iniciado na porta 8080";
$server->run();

?>