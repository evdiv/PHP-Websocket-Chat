<?php
namespace Chat;

class User {
	private $id = 0;
	private $name = '';
	private $email = '';
	private $active = 0;
	private $login_date;
	private $db;

	function __construct() {
		$this->db = new Database;
	}

	public function setName($name = ''){
		$this->name = filter_var($name, FILTER_SANITIZE_STRING);
	}

	public function setEmail($email = ''){
		$this->email = filter_var($email, FILTER_SANITIZE_EMAIL);
	}

	public function save() {
		$this->name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
		$this->email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
		$this->active = 1;
		$this->login_date = date('Y-m-d h:i:s');
	}

	public function store() {
		$stmt = $this->db->prepare("INSERT INTO `users` (`name`, `email`, `active`, `login_date`)
									VALUES(:name, :email, :active, :login_date)");

		$stmt->bindParam(":name", $this->name);
		$stmt->bindParam(":email", $this->email);
		$stmt->bindParam(":active", $this->active);
		$stmt->bindParam(":login_date", $this->login_date);

		try{
			return !!$stmt->execute();
		} catch(Exception $e){
			die($e->getMessage());
		}
	}

	public function getById($id = 0){
		$stmt = $this->db->prepare("SELECT * FROM users WHERE Id = :id");
		$stmt->bindParam(":id", $id);

		try{
			if($stmt->execute()){
				$user = $stmt->fetch(\PDO::FETCH_ASSOC);

				$this->id = $user['Id'];
				$this->name = $user['name'];
				$this->email = $user['email'];
				$this->active = $user['active'];
				$this->login_date = $user['login_date'];

				return $this->id;
			}
		} catch(Exception $e){
			die($e->getMessage());
		}
	}

	public function getByEmail($email = ''){
		$email = !empty($email) ? $email : $this->email;
		$stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
		$stmt->bindParam(":email", $email);

		try{
			if($stmt->execute()){
				$user = $stmt->fetch(\PDO::FETCH_ASSOC);

				$this->id = $user['Id'];
				$this->name = $user['name'];
				$this->email = $user['email'];
				$this->active = $user['active'];
				$this->login_date = $user['login_date']; 

				return $this->id;
			}
		} catch(Exception $e){
			die($e->getMessage());
		}
	}

	public function logIn($id = 0) {
		$id = !empty($id) ? $id : $this->id;
		$this->login_date = date('Y-m-d h:i:s');

		$stmt = $this->db->prepare("UPDATE users 
									SET active = 1, login_date = :login_date
									WHERE Id = :id");

		$stmt->bindParam(":login_date", $this->login_date);
		$stmt->bindParam(":id", $id);

		try{
			return $stmt->execute();
		} catch(Exception $e){
			die($e->getMessage());
		}
	}

	public function logOut($id = 0) {
		$id = !empty($id) ? $id : $this->id;
		$stmt = $this->db->prepare("UPDATE users SET active = 0 WHERE Id = :id");

		$stmt->bindParam(":id", $id);

		try{
			return $stmt->execute();
		} catch(Exception $e){
			die($e->getMessage());
		}
	}




}