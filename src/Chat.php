<?php
namespace Chat;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;


class Chat implements MessageComponentInterface {
    protected $clients;


    public function __construct() {
        $this->clients = new \SplObjectStorage;
        echo 'Chat Server started!';
    }


    public function onOpen(ConnectionInterface $conn) {

        $querystring = $conn->httpRequest->getUri()->getQuery();
        parse_str($querystring, $queryarray);

        $queryarray = (new Request())->validate($queryarray, array('token'));

        if(!$queryarray) {
            echo "Error: Token is not provided for connection: ({$conn->resourceId})\n";
            return;
        }

        $ChatRoom = (new ChatRoom())->getByToken($queryarray['token']);

        $conn->chatRoom = $ChatRoom->toArray();
        $conn->user = $ChatRoom->user()->toArray();

        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId}) UserId: {$conn->user['id']}  ChatRoomId: {$conn->chatRoom['id']}\n";
    }


    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;

        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($this->clients as $client) {

            if ($from == $client) {
                $Message = new Message();
                $Message->setChatId($client->chatRoom['id']);
                $Message->setFrom($client->user['id']);
                $Message->setMsgText($msg);
                $Message->store();
            } else {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
            }
        }
    }


    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }


    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}