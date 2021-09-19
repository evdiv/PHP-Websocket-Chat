<?php
namespace Chat;

class ChatRoom {

	use ArrayAwareTrait;

	private $id = 0;
	private $user_id = 0;
	private $admin_id = 0;
	private $active = 0;
	private $token = '';
	private $created_at;

	private $db;

	function __construct() {
		$this->db = (new Database)->get();
	}

	public function setUserId($user_id){
		$this->user_id = intval($user_id);
		return $this;
	}

	public function setAdminId($admin_id){
		$this->admin_id = intval($admin_id);
		return $this;
	}

	public function getId(){
		return $this->id;
	}

	public function getToken(){
		return $this->token;
	}

	public function getUserId(){
		return $this->user_id;
	}

	public function getAdminId(){
		return $this->admin_id;
	}

	public function getRecipientId($from_user_id){
		return ($from_user_id == $this->admin_id) ? $this->user_id : $this->admin_id;
	}

	public function store() {
		$this->active = 1;
		$this->token = Request::generateToken();

		$stmt = $this->db->prepare("INSERT INTO `chat_rooms` (`user_id`, `admin_id`, `active`, `token`)
											VALUES(:user_id, :admin_id, :active, :token)");

		$stmt->bindParam(":user_id", $this->user_id);
		$stmt->bindParam(":admin_id", $this->admin_id);
		$stmt->bindParam(":active", $this->active);
		$stmt->bindParam(":token", $this->token);

		try{
			$stmt->execute();
			$this->id = $this->db->lastInsertId();
			return $this;
			
		} catch(Exception $e){
			die($e->getMessage());
		}
	}
	
	public function getById($id = 0){
		$stmt = $this->db->prepare("SELECT * FROM chat_rooms WHERE id = :id");
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

	public function getByToken($token = ''){
		$stmt = $this->db->prepare("SELECT * FROM chat_rooms WHERE token = :token");
		$stmt->bindParam(":token", $token);

		try{
			if($stmt->execute()){
				$row = $stmt->fetch(\PDO::FETCH_ASSOC);
				return $this->populate($row);
			}
		} catch(Exception $e){
			die($e->getMessage());
		}
	}

	public function attachAdmin($admin_id = 0){
		if(empty($admin_id)) {
			return;
		}
		$this->setAdminId($admin_id);

		$stmt = $this->db->prepare("UPDATE chat_rooms SET admin_id = :admin_id WHERE id = :id");
		$stmt->bindParam(":admin_id", $this->admin_id);
		$stmt->bindParam(":id", $this->id);

		try{
			if($stmt->execute()){
				return $stmt->rowCount();
			}

		} catch(Exception $e){
			die($e->getMessage());
		}
	}


	public function close($id = 0){
		$stmt = $this->db->prepare("UPDATE chat_rooms SET active = 0 WHERE id = :id");
		$stmt->bindParam(":id", $id);

		try{
			if($stmt->execute()){
				return $stmt->rowCount();
			}

		} catch(Exception $e){
			die($e->getMessage());
		}
	}


	public function user(){
		return (new User())->getById($this->user_id);
	}

	public function admin(){
		return (new User())->getById($this->admin_id);
	}

	public function messages(){
		return (new Message())->getByChatRoomId($this->id);
	}
}