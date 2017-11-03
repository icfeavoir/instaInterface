<?php
	session_start();
	require_once('db.php');

    foreach ($_POST as $key => $value) {
		$_SESSION[$key] = $value;
		setcookie($key, $value, time()+30*24*3600, null, null, false, true);
	}

    $email = $_POST['email'] ?? $_COOKIE['email'] ?? "";
    $password = $_POST['password'] ?? $_COOKIE['password'] ?? "";

    $statement = $db->prepare('SELECT * FROM User WHERE email=:email LIMIT 1');
	$statement->execute(array(':email'=>$email));
	$user = $statement->fetch(PDO::FETCH_ASSOC);

	$_SESSION['ID'] = $user['ID'];
	setcookie('ID', $user['ID'], time()+30*24*3600, null, null, false, true);

	if($statement->rowCount() == 0){
		echo "wrong email";
	}else if(!password_verify($password, $user['password'])){
		echo "wrong pass";
	}else{
		// connected
		if($user['rights']&1){
			header('Location: manage/');
		}else if($user['rights']&2){
			echo "login YouPic";
			// header('Location: thread/login.php');
		}else{
			echo 'internal error';
		}
	}