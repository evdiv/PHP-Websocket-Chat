<?php 
namespace Chat;

class Request {
	private $jsonData;

	function __construct(){
		$this->jsonData = json_decode(trim(file_get_contents('php://input')), true);
	}

	public function get(){
		return filter_var_array($this->jsonData, FILTER_SANITIZE_STRING);  
	}
}
