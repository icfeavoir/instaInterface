<?php
	session_start();
	require_once('db.php');

    $email = $_POST['email'] ?? $_SESSION['email'] ?? "";
    $password = $_POST['password'] ?? $_SESSION['password'] ?? "";

    $statement = $db->prepare('SELECT * FROM instagram.User WHERE email=:email LIMIT 1');
	$statement->execute(array(':email'=>$email));
	$user = $statement->fetch(PDO::FETCH_ASSOC);

	$user['ID'] = $user['instaface_id'];
	foreach ($user as $key => $value) {
		$_SESSION[$key] = $value;
		setcookie($key, $value, time()+30*24*3600, PATH, null, false, true);
	}

	// the password not hashed
	setcookie('password', $password, time()+30*24*3600, PATH, null, false, true);

	if($statement->rowCount() == 0){
		header('Location: index.php?err=0');
	}else if(!password_verify($password, $user['password'])){
		header('Location: index.php?err=1');
	}else{
		// connected
		if($user['rights']&1){
			header('Location: manage/');
		}else if($user['rights']&2){
			header('Location: manage/admin.php');
			// header('Location: thread/login.php');
		}else{
			echo 'internal error';
		}
	}