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

        foreach ($this->clients as $client) {
            if (!empty($client->user['admin']) && $conn->user['id'] !== $client->user['id']) {
                $data = array('action' => 'addUser', 'chatRoom' => $conn->chatRoom, 'user' => $conn->user);
                $client->send(json_encode($data));
            }
        }

        echo "New connection! ({$conn->resourceId}) UserId: {$conn->user['id']}  ChatRoomId: {$conn->chatRoom['id']}\n";
    }


    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;

        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');


        if(!empty($from->user['admin']) && Request::isJson($msg)){

            $msgArr = json_decode($msg, true);

            $msg = $msgArr['msg'];
            $chatRoomToken = $msgArr['chatRoomToken'];

            $ChatRoom = (new ChatRoom())->getByToken($chatRoomToken);

            if($ChatRoom->getAdminId() !== $from->user['id']){
                $ChatRoom->attachAdmin($from->user['id']);
            }

            $chatRoomId = $ChatRoom->getId();

            echo "Chat Room ID: " . $chatRoomId . "\n";

        } else {
            $ChatRoom = (new ChatRoom())->getById($from->chatRoom['id']);
            $chatRoomId = $from->chatRoom['id'];

            echo "Chat Room ID: " . $chatRoomId . "\n";
        }

        //Get the receipient from the Chat Room
        $recipientId = $ChatRoom->getRecipientId($from->user['id']);

        //Store the Message in the db
        $Message = new Message();
        $Message->setChatId($chatRoomId);
        $Message->setFromUserId($from->user['id']);
        $Message->setMsgText($msg);
        $Message->store();

        echo "This message is intended for userID: " . $recipientId . "\n";

        foreach ($this->clients as $client) {

            echo "Looping throug clients. Client ID " . $client->user['id'] . "\n";

            if($client->user['id'] == $from->user['id']){
                continue;
            }

            $data = array('action' => 'addMessage', 'msg' => $msg, 'chatRoom' => $from->chatRoom, 'user' => $from->user);

            //Sending message to all admins
            if (empty($recipientId) && !empty($client->user['admin'])) {
                $client->send(json_encode($data));
            
            //Sending message to attached admin
            } elseif($recipientId == $client->user['id'] && !empty($client->user['admin'])){
                $client->send(json_encode($data));
           
            //Sending message to user
            } elseif($recipientId == $client->user['id']){

                $data = array('action' => 'addMessage', 'msg' => $msg, 'user' => array('name' => $from->user['name']));
                $client->send(json_encode($data));
            }
        }
    }


    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        (new ChatRoom())->close($conn->chatRoom['id']);
        (new User())->logOut($conn->user['id']);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }


    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}