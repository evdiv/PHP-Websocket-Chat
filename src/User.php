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
	private $token = '';
	private $errors = [];
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

	public function setToken($token = ''){
		$this->token = filter_var($token, FILTER_SANITIZE_STRING);
	}

	public function getName(){
		return $this->name;
	}

	public function getEmail(){
		return $this->email;
	}

	public function getToken(){
		return $this->token;
	}

	public function getErrors(){
		return $this->errors;
	}


	public function store($request) {

		$this->name = $request['name'];
		$this->email = $request['email'];
		$this->token = $this->generateToken();
		$this->login_date = date('Y-m-d h:i:s');
		$this->active = 1;

		$stmt = $this->db->prepare("INSERT INTO `users` (`name`, `email`, `login_date`, `active`, `token`)
											VALUES(:name, :email, :login_date, :active, :token)");

		$stmt->bindParam(":name", $this->name);
		$stmt->bindParam(":email", $this->email);
		$stmt->bindParam(":login_date", $this->login_date);
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

	public function getByEmail($email){
		$stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
		$stmt->bindParam(":email", $email);

		try{
			if($stmt->execute()){
				$row = $stmt->fetch(\PDO::FETCH_ASSOC);
				return $this->populate($row);
			}
		} catch(Exception $e){
			die($e->getMessage());
		}
	}

	public function logIn($id = 0) {
		$this->id = !empty($id) ? $id : $this->id;
		$this->login_date = date('Y-m-d h:i:s');
		$this->active = 1;
		$this->token = $this->generateToken();

		return $this->update();
	}

	public function logOut($id = 0) {
		$this->id = !empty($id) ? $id : $this->id;
		$this->active = 0;
		$this->token = '';

		return $this->update();
	}


	private function update() {
		$stmt = $this->db->prepare("UPDATE users 
									SET active = :active, 
										token = :token,
										login_date = :login_date
									WHERE id = :id");


		$stmt->bindParam(":active", $this->active);
		$stmt->bindParam(":login_date", $this->login_date);
		$stmt->bindParam(":token", $this->token);
		$stmt->bindParam(":id", $this->id);

		try{
			$stmt->execute();
			return $stmt->rowCount();
		} catch(Exception $e){
			die($e->getMessage());
		}
	}

	private function generateToken($length = 16){
		$bytes = openssl_random_pseudo_bytes($length);
    	return bin2hex($bytes);
	}
}