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
		if(isset($_POST['username']) && isset($_POST['password']) && $_POST['username'] != "" && $_POST['password'] != ""){
			// no auto increment...
			$lastID = $db->prepare('SELECT account_id FROM scraping2.Account ORDER BY account_id DESC LIMIT 1');
			$lastID->execute();
			$lastID = $lastID->fetch()['account_id'];
			$statement = $db->prepare('INSERT INTO scraping2.Account (account_id, username, password, instaface_id, read_inbox, send_messages) VALUES(:newID, :username, :password, :instaface_id, true, true)');
			$statement->execute(array(':newID'=>$lastID+1, ':username'=>$_POST['username'], ':password'=>$_POST['password'], ':instaface_id'=>$_SESSION['ID']));
		}
		header('Location: index.php');
	}else if($action == 'editAccount'){
		if(isset($_GET['ID']) && isset($_POST['username']) && isset($_POST['password']) && $_POST['username'] != "" && $_POST['password'] != ""){
			$statement = $db->prepare('UPDATE scraping2.Account SET username=:username, password=:password WHERE account_id=:ID LIMIT 1');
			$statement->execute(array(':username'=>$_POST['username'], ':password'=>$_POST['password'], ':ID'=>$_GET['ID']));
		}
		header('Location: index.php');
	}else if($action == 'deleteAccount'){
		if(isset($_POST['ID'])){
			$statement = $db->prepare('DELETE FROM scraping2.Account WHERE account_id=:ID LIMIT 1');
			$statement->execute(array(':ID'=>$_POST['ID']));
		}
	}else if($action == 'getStats'){
		$json = array();
		if(isset($_POST['accountID'])){
			$statement = $db->prepare('SELECT DATE(FROM_UNIXTIME(timestamp/1000000)) as date FROM scraping2.ThreadItem WHERE thread_id IN (SELECT thread_id FROM scraping2.Thread WHERE account_id=:accountID) AND response=false ORDER BY timestamp');
			$statement->execute(array(':accountID'=>$_POST['accountID']));
			$json['sent'] = $statement->fetchAll(PDO::FETCH_ASSOC);
			$statement = $db->prepare('SELECT DATE(FROM_UNIXTIME(timestamp/1000000)) as date FROM scraping2.ThreadItem WHERE thread_id IN (SELECT thread_id FROM scraping2.Thread WHERE account_id=:accountID) AND response=true ORDER BY timestamp');
			$statement->execute(array(':accountID'=>$_POST['accountID']));
			$json['received'] = $statement->fetchAll(PDO::FETCH_ASSOC);
		}
		echo json_encode($json);
	}

// ADMIN

	if($action == 'saveUser'){
		if(isset($_POST['email']) && isset($_POST['password']) && $_POST['email'] != "" && $_POST['password'] != ""){
			$statement = $db->prepare('INSERT INTO instagram.User (email, password, rights) VALUES(:email, :password, :rights)');
			$statement->execute(array(':email'=>$_POST['email'], ':password'=>password_hash($_POST['password'], PASSWORD_DEFAULT), ':rights'=>$_POST['rights']));
		}
		header('Location: admin.php');
	}else if($action == 'editUser'){
		if(isset($_GET['ID']) && isset($_POST['email']) && isset($_POST['password']) && $_POST['email'] != "" && $_POST['password'] != ""){
			$statement = $db->prepare('UPDATE instagram.User SET email=:email, password=:password, rights=:rights WHERE instaface_id=:ID LIMIT 1');
			$statement->execute(array(':email'=>$_POST['email'], ':password'=>password_hash($_POST['password'], PASSWORD_DEFAULT), ':rights'=>$_POST['rights'], ':ID'=>$_GET['ID']));
		}
		header('Location: admin.php');
	}else if($action == 'deleteUser'){
		if(isset($_POST['ID'])){
			$statement = $db->prepare('DELETE FROM instagram.User WHERE instaface_id=:ID LIMIT 1');
			$statement->execute(array(':ID'=>$_POST['ID']));
		}
	}else if($action == 'getTotalNumbers'){
		$json = array();
		if(isset($_POST['userID'])){
			$statement = $db->prepare('SELECT COUNT(*) AS nb FROM scraping2.ThreadItem WHERE thread_id IN (SELECT thread_id FROM scraping2.Thread WHERE account_id IN (SELECT account_id FROM scraping2.Account WHERE instaface_id=:userID)) AND response=false');
			$statement->execute(array(':userID'=>$_POST['userID']));
			$json['sent'] = $statement->fetch(PDO::FETCH_ASSOC)['nb'];
			$statement = $db->prepare('SELECT COUNT(*) AS nb FROM scraping2.ThreadItem WHERE thread_id IN (SELECT thread_id FROM scraping2.Thread WHERE account_id IN (SELECT account_id FROM scraping2.Account WHERE instaface_id=:userID)) AND response=true');
			$statement->execute(array(':userID'=>$_POST['userID']));
			$json['received'] = $statement->fetch(PDO::FETCH_ASSOC)['nb'];
		}
		echo json_encode($json);
	}

	else if($action == 'logout'){
		session_destroy();
		setcookie('ID', 0, time()+30*24*3600, PATH, null, false, true);
		header('Location: '.PATH);
	}