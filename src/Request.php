<?php 
namespace Chat;

class Request {
	
	private $jsonData;
	private $errors = array();

	function __construct(){
		$this->jsonData = json_decode(trim(file_get_contents('php://input')), true);
	}

	public function get(){
		return filter_var_array($this->jsonData, FILTER_SANITIZE_STRING);  
	}

	public function getErrors(){
		return $this->errors;
	}


	/**
	 * Validate incoming parameters
	 * Can be extended with dinamic rules in the future
	 *
	 * @param array   $request   
	 * @param array or string $fields
	 * 
	 * @return cleaned request array or false
	 */ 
	public function validate($request, $fields) {
		$this->errors = array();
		$output = array();

		if(is_string($fields)) {
			$fields = array($fields);
		}

		if(in_array('id', $fields)){
			if(empty($request['id'])){
				$this->errors[] = 'Id is required';
			} elseif(!is_numeric($request['id'])) {
				$this->errors[] = 'Id nas to be a number';
			} else{
				$output['id'] = intval($request['id']);
			}
		}

		if(in_array('name', $fields)){
			if(empty($request['name'])){
				$this->errors[] = 'User name is required';
			} elseif(strlen($request['name']) < 3) {
				$this->errors[] = 'User Name has to be at least 3 characters long';
			} else{
				$output['name'] = filter_var($request['name'], FILTER_SANITIZE_STRING);  
			}
		}

		if(in_array('email', $fields)){
			if(empty($request['email'])){
				$this->errors[] = 'User email is required';
			} elseif(!filter_var($request['email'], FILTER_VALIDATE_EMAIL)) {
				$this->errors[] = 'User Email is not valid';
			} else{
				$output['email'] = filter_var($request['email'], FILTER_SANITIZE_EMAIL);  
			}
		}

		if(in_array('token', $fields)){

			// Check only if the token is not empty
			// More rules can be added later
			if(empty($request['token'])){
				$this->errors[] = 'Token is required';
			}  else{
				$output['token'] = filter_var($request['token'], FILTER_SANITIZE_STRING);  
			}
		}


		return empty($this->errors) ? $output : false;
	}


	// Rules for generating the token
	public static function generateToken($length = 16){
		// Generate random string
		// Can be switched to JWT later
		$bytes = openssl_random_pseudo_bytes($length);
    	return bin2hex($bytes);
	}


	public static function isJson($string) {
	   json_decode($string);
	   return json_last_error() === JSON_ERROR_NONE;
	}
}
