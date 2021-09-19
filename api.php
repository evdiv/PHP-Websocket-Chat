<?php

require './vendor/autoload.php';

$Request = new Chat\Request();
$request = $Request->get();


if($request['action'] == 'logIn'){
	$request = $Request->validate($request, array('name', 'email'));

	if($Request->getErrors()){
		echo json_encode(array('errors' => $Request->getErrors()));
		exit;
	}

	$User = new Chat\User;
	if($User->getByEmail($request['email'])) {
		$User->logIn();
	} else {
		$User->store($request);
	}

	$ChatRoom = new Chat\ChatRoom;
	$ChatRoom->setUserId($User->getId());
	$ChatRoom->store();

	echo json_encode(array('token' => $ChatRoom->getToken()));

}