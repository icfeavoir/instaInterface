<?php
	session_start();
	require_once('../db.php');

	if(empty($_SESSION))
		exit('Not connected');
	if(!isset($_GET['action']))
		exit('No action');

	$action = $_GET['action'];

// USER

	if($action == 'saveAccount'){
		if(isset($_POST['email']) && isset($_POST['password']) && $_POST['email'] != "" && $_POST['password'] != ""){
			$statement = $db->prepare('INSERT INTO Account (email, password, user_id) VALUES(:email, :password, :user_id)');
			$statement->execute(array(':email'=>$_POST['email'], ':password'=>$_POST['password'], ':user_id'=>$_SESSION['ID']));
		}
		header('Location: index.php');
	}else if($action == 'editAccount'){
		if(isset($_GET['ID']) && isset($_POST['email']) && isset($_POST['password']) && $_POST['email'] != "" && $_POST['password'] != ""){
			$statement = $db->prepare('UPDATE Account SET email=:email, password=:password WHERE ID=:ID LIMIT 1');
			$statement->execute(array(':email'=>$_POST['email'], ':password'=>$_POST['password'], ':ID'=>$_GET['ID']));
		}
		header('Location: index.php');
	}else if($action == 'deleteAccount'){
		if(isset($_POST['ID'])){
			$statement = $db->prepare('DELETE FROM Account WHERE ID=:ID LIMIT 1');
			$statement->execute(array(':ID'=>$_POST['ID']));
		}
	}else if($action == 'getStats'){
		$json = array();
		if(isset($_POST['accountID'])){
			$statement = $db->prepare('SELECT COUNT(*) AS nb FROM Account WHERE ID=:accountID');
			$statement->execute(array(':accountID'=>$_POST['accountID']));
			$json['sent'] = $statement->fetch()['nb'];
			$statement = $db->prepare('SELECT COUNT(*) AS nb FROM Account WHERE ID=:accountID');
			$statement->execute(array(':accountID'=>$_POST['accountID']));
			$json['received'] = $statement->fetch()['nb'];
		}
		echo json_encode($json);
	}

// ADMIN

	if($action == 'saveUser'){
		if(isset($_POST['email']) && isset($_POST['password']) && $_POST['email'] != "" && $_POST['password'] != ""){
			$statement = $db->prepare('INSERT INTO User (email, password, rights) VALUES(:email, :password, :rights)');
			$statement->execute(array(':email'=>$_POST['email'], ':password'=>password_hash($_POST['password'], PASSWORD_DEFAULT), ':rights'=>$_POST['rights']));
		}
		header('Location: admin.php');
	}else if($action == 'editUser'){
		if(isset($_GET['ID']) && isset($_POST['email']) && isset($_POST['password']) && $_POST['email'] != "" && $_POST['password'] != ""){
			$statement = $db->prepare('UPDATE User SET email=:email, password=:password WHERE ID=:ID LIMIT 1');
			$statement->execute(array(':email'=>$_POST['email'], ':password'=>$_POST['password'], ':ID'=>$_GET['ID']));
		}
		header('Location: index.php');
	}else if($action == 'deleteUser'){
		if(isset($_POST['ID'])){
			$statement = $db->prepare('DELETE FROM User WHERE ID=:ID LIMIT 1');
			$statement->execute(array(':ID'=>$_POST['ID']));
		}
	}else if($action == 'getStats'){
		$json = array();
		if(isset($_POST['accountID'])){
			$statement = $db->prepare('SELECT COUNT(*) AS nb FROM Account WHERE ID=:accountID');
			$statement->execute(array(':accountID'=>$_POST['accountID']));
			$json['sent'] = $statement->fetch()['nb'];
			$statement = $db->prepare('SELECT COUNT(*) AS nb FROM Account WHERE ID=:accountID');
			$statement->execute(array(':accountID'=>$_POST['accountID']));
			$json['received'] = $statement->fetch()['nb'];
		}
		echo json_encode($json);
	}

	else if($action == 'logout'){
		session_destroy();
		setcookie('ID', 0, time()+30*24*3600, '/', null, false, true);
		header('Location: /');
	}