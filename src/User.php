<?php
namespace Chat;

class User {

	use ArrayAwareTrait;

	private $id = 0;
	private $admin = 0;
	private $name = '';
	private $email = '';
	private $active = 0;
	private $login_date;
	private $db;

	function __construct() {
		$this->db = (new Database)->get();
	}

	public function setName($name = ''){
		$this->name = filter_var($name, FILTER_SANITIZE_STRING);
	}

	public function setEmail($email = ''){
		$this->email = filter_var($email, FILTER_SANITIZE_EMAIL);
	}

	public function getId(){
		return $this->id;
	}

	public function getName(){
		return $this->name;
	}

	public function getEmail(){
		return $this->email;
	}


	public function store($request) {

		$this->name = $request['name'];
		$this->email = $request['email'];
		$this->login_date = date('Y-m-d h:i:s');
		$this->active = 1;

		$stmt = $this->db->prepare("INSERT INTO `users` (`name`, `email`, `login_date`, `active`)
											VALUES(:name, :email, :login_date, :active)");

		$stmt->bindParam(":name", $this->name);
		$stmt->bindParam(":email", $this->email);
		$stmt->bindParam(":login_date", $this->login_date);
		$stmt->bindParam(":active", $this->active);

		try{
			$stmt->execute();
			$this->id = $this->db->lastInsertId();
			return $this;
			
		} catch(Exception $e){
			die($e->getMessage());
		}
	}


	public function getAllActive($id = 0){
		$stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
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

	
	public function getById($id = 0){
		$stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
		$stmt->bindParam(":id", $id);

		try{
			if($stmt->execute()){
				$row = $stmt->fetch(\PDO::FETCH_ASSOC);
				return !empty($row) ? $this->populate($row) : false;
			}

		} catch(Exception $e){
			die($e->getMessage());
		}
	}

	public function getByEmail($email){
		$stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
		$stmt->bindParam(":email", $email);

		try{
			if($stmt->execute()){
				$row = $stmt->fetch(\PDO::FETCH_ASSOC);
				return !empty($row) ? $this->populate($row) : false;
			}
		} catch(Exception $e){
			die($e->getMessage());
		}
	}

	public function logIn($id = 0) {
		$this->id = !empty($id) ? $id : $this->id;
		$this->login_date = date('Y-m-d h:i:s');
		$this->active = 1;

		return $this->update();
	}

	public function logOut($id = 0) {
		$this->id = !empty($id) ? $id : $this->id;
		$this->active = 0;

		return $this->update();
	}


	private function update() {
		$stmt = $this->db->prepare("UPDATE users 
									SET active = :active, 
										login_date = :login_date
									WHERE id = :id");


		$stmt->bindParam(":active", $this->active);
		$stmt->bindParam(":login_date", $this->login_date);
		$stmt->bindParam(":id", $this->id);

		try{
			$stmt->execute();
			return $stmt->rowCount();
		} catch(Exception $e){
			die($e->getMessage());
		}
	}
}