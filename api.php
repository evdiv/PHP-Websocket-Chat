<?php

require './vendor/autoload.php';

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS);

if($action == 'save'){
	$User = new Chat\User;
	$User->save();
	if($User->getByEmail()){
		$User->logIn();
	} else {
		$User->store();
	}
}