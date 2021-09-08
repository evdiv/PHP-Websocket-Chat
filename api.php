<?php
session_start();
require './vendor/autoload.php';

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS);

if($action == 'save'){
	$User = new Chat\User;
	$User->save();
	($User->getByEmail()) ? $User->logIn() : $User->store();

	$_SESSION['ChatUser'] = $User->toArray();
	header('Location: /client.php');
}