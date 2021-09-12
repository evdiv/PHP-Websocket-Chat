<?php 
namespace Chat;

trait ErrorsAwareTrait 
{
	private $errors = array();


	public function resetErrors() {
		$this->errors = array();
	}

	public function addError($err = ''){
		if(!empty($err)){
			$this->errors[] = $err;
		}
	}

	public function getErrors() {
		return $this->errors;
	}
}
