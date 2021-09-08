<?php
namespace Chat;

class Database {
	private $host ='localhost';
	private $db = 'chat';
	private $user = 'root';
	private $password = '';
	private $conn;


	function __construct() {
		if(!$this->conn){
			$this->conn = $this->connect();
		}
	}

	public function get() {
		return $this->conn;
	}

	private function connect() {
		try{
			$conn = new \PDO('mysql:host=' . $this->host . '; dbname=' . $this->db, $this->user, $this->password);
			$conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			return $conn;
		
		} catch(PDOException $e) {
			die('Database Error: ' . $e->getMessage());
		}
	}
}