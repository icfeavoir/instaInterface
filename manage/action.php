<?php
	session_start();
	require_once('../db.php');

	if(empty($_SESSION))
		exit('Not connected');
	if(!isset($_GET['action']))
		exit('No action');

	$action = $_GET['action'];

	if($action == 'saveAccount'){
		if(isset($_POST['email']) && isset($_POST['password']) && $_POST['email'] != "" && $_POST['password'] != ""){
			$statement = $db->prepare('INSERT INTO Account (email, password, user_id) VALUES(:email, :password, :user_id)');
			$statement->execute(array(':email'=>$_POST['email'], ':password'=>$_POST['password'], ':user_id'=>$_SESSION['ID']));
		}
		header('Location: index.php');
	}