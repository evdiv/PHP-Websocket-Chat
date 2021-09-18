<?php
namespace Chat;

class Message {

	use ArrayAwareTrait;

	private $id = 0;
	private $chat_room_id = 0;
	private $from_user_id = 0;
	private $msg_text = '';
	private $created_at;

	private $db;

	function __construct() {
		$this->db = (new Database)->get();
	}

	public function getId(){
		return $this->id;
	}

	public function setChatId($chat_room_id){
		$this->chat_room_id = $chat_room_id;
	}

	public function setFromUserId($from_user_id){
		$this->from_user_id = $from_user_id;
	}	

	public function setMsgText($msg_text){
		$this->msg_text = $msg_text;
	}		

	public function store() {

		$stmt = $this->db->prepare("INSERT INTO `messages` (`chat_room_id`, `from_user_id`, `msg_text`) 
											VALUES(:chat_room_id, :from_user_id, :msg_text)");

		$stmt->bindParam(":chat_room_id", $this->chat_room_id);
		$stmt->bindParam(":from_user_id", $this->from_user_id);
		$stmt->bindParam(":msg_text", $this->msg_text);

		try{
			$stmt->execute();
			$this->id = $this->db->lastInsertId();
			return $this;
			
		} catch(Exception $e){
			die($e->getMessage());
		}
	}
	
	public function getById($id = 0){
		$stmt = $this->db->prepare("SELECT * FROM messages WHERE id = :id");
		$stmt->bindParam(":id", $id);

		try{
			if($stmt->execute()){
				$row = $stmt->fetch(\PDO::FETCH_ASSOC);
				return $this->populate($row);
			}

		} catch(Exception $e){
			die($e->getMessage());
		}
	}

	public function getByChatRoomId($chat_room_id){
		$stmt = $this->db->prepare("SELECT * FROM messages WHERE chat_room_id = :chat_room_id");
		$stmt->bindParam(":chat_room_id", $chat_room_id);

		try{
			if($stmt->execute()){
				return $stmt->fetchAll(\PDO::FETCH_ASSOC);
			}

		} catch(Exception $e){
			die($e->getMessage());
		}		
	}
}