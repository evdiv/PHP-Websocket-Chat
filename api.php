<?php

require './vendor/autoload.php';

$request = (new Chat\Request())->get();


if($request['action'] == 'logIn'){

	$User = new Chat\User;
	$User->save($request);

	if($User->getByEmail()) {
		$User->logIn();
	} else {
		$User->store();
	}

	echo json_encode(array('id' => $User->id));
}