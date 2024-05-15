<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class YourWebSocketHandler implements MessageComponentInterface
{
    public function onOpen(ConnectionInterface $conn)
    {
        echo "Nouvelle connexion WebSocket : {$conn->resourceId}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo "Message reçu de {$from->resourceId} : $msg\n";
        // Traitez le message reçu ici
        $from->send("Message reçu : $msg");
    }

    public function onClose(ConnectionInterface $conn)
    {
        echo "Connexion WebSocket fermée : {$conn->resourceId}\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Erreur WebSocket : {$e->getMessage()}\n";
        $conn->close();
    }
}

// Configuration du serveur WebSocket
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new YourWebSocketHandler()
        )
    ),
    8080 // Port sur lequel le serveur écoutera les connexions WebSocket
);

// Démarrage du serveur WebSocket
echo "Serveur WebSocket en cours d'exécution...\n";
$server->run();
