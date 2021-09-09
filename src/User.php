<?php
namespace Chat;

class User {

	use ArrayAwareTrait;

	public $id = 0;
	public $name = '';
	public $email = '';
	public $active = 0;
	public $login_date;
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

	public function save($request = array()) {
		$this->name = !empty($request['name']) ? $request['name'] : '';
		$this->email = !empty($request['email']) ? $request['email'] : '';
		$this->login_date = date('Y-m-d h:i:s');
	}

	public function store() {
		$stmt = $this->db->prepare("INSERT INTO `users` (`name`, `email`, `login_date`, `active`)
											VALUES(:name, :email, :login_date, 1)");

		$stmt->bindParam(":name", $this->name);
		$stmt->bindParam(":email", $this->email);
		$stmt->bindParam(":login_date", $this->login_date);

		try{
			$stmt->execute();
			$this->id = $this->db->lastInsertId();
			return $this->id;
			
		} catch(Exception $e){
			die($e->getMessage());
		}
	}
	
	public function getById($id = 0){
		$stmt = $this->db->prepare("SELECT * FROM users WHERE Id = :id");
		$stmt->bindParam(":id", $id);

		try{
			if($stmt->execute()){
				$row = $stmt->fetch(\PDO::FETCH_ASSOC);
				if($row){
					$this->id = $row['Id'];
					$this->name = $row['name'];
					$this->email = $row['email'];
					$this->active = $row['active'];
					$this->login_date = $row['login_date'];

					return $this;
				}
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
				$row = $stmt->fetch(\PDO::FETCH_ASSOC);
				if($row) {
					$this->id = $row['Id'];
					$this->name = $row['name'];
					$this->email = $row['email'];
					$this->active = $row['active'];
					$this->login_date = $row['login_date']; 

					return $this;
				}
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
			$stmt->execute();
			return $stmt->rowCount();
		} catch(Exception $e){
			die($e->getMessage());
		}
	}

	public function logOut($id = 0) {
		$id = !empty($id) ? $id : $this->id;
		$stmt = $this->db->prepare("UPDATE users SET active = 0 WHERE Id = :id");

		$stmt->bindParam(":id", $id);

		try{
			$stmt->execute();
			return $stmt->rowCount();
		} catch(Exception $e){
			die($e->getMessage());
		}
	}
}