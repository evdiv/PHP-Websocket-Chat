<?php
namespace Chat;

class ChatRoom {

	use ArrayAwareTrait;

	private $id = 0;
	private $user_id = 0;
	private $active = 0;
	private $token = '';
	private $created_at;

	private $db;

	function __construct() {
		$this->db = (new Database)->get();
	}

	public function getId(){
		return $this->id;
	}

	public function getToken(){
		return $this->token;
	}

	public function store($user_id) {

		$this->user_id = $user_id;
		$this->active = 1;
		$this->token = Request::generateToken();
		$this->created_at = date('Y-m-d h:i:s');

		$stmt = $this->db->prepare("INSERT INTO `chat_rooms` (`user_id`, `active`, `token`, `created_at`)
											VALUES(:user_id, :active, :token, :created_at)");

		$stmt->bindParam(":user_id", $this->user_id);
		$stmt->bindParam(":active", $this->active);
		$stmt->bindParam(":token", $this->token);
		$stmt->bindParam(":created_at", $this->created_at);

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

	public function getByToken($token){
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

	public function user(){
		return (new User())->getById($this->user_id);
	}
}