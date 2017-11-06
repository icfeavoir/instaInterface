<?php
	session_start();
	if(empty($_SESSION)){
		foreach ($_COOKIE as $key => $value) {
			$_SESSION[$key] = $value;
		}
	}
	if(!isset($_SESSION['ID']) || $_SESSION['ID'] == 0)
		header('Location: /index.php');

	// edit or add
	require_once('../db.php');
	if(isset($_POST['accountID'])){
		$account = $db->prepare('SELECT * FROM Account WHERE ID=:id');
		$account->execute(array(':id'=>$_POST['accountID']));
		$account = $account->fetch();
	}else{
		$account = array();
	}
	$email = $account['email'] ?? '';
	$password = $account['password'] ?? '';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title><?php echo (isset($_POST['accountID']) ? 'Edit' : 'Add'); ?> an Instagram Account</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    </head>

    <body class="text-center">
		<div class="alert alert-info text-center">
			<strong><?php echo (isset($_POST['accountID']) ? 'Edit' : 'Add'); ?> an Instagram account</strong>
		</div>

		<form action="action.php?action=<?php echo (isset($_POST['accountID']) ? 'editAccount&ID='.$_POST['accountID'] : 'saveAccount'); ?>" method="POST">
			<div class="input-group">
				<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
				<input required id="email" type="text" class="form-control" name="email" placeholder="Email" value="<?php echo $email; ?>">
			</div>
			<div class="input-group">
				<span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
				<input required id="password" type="text" class="form-control" name="password" placeholder="Password" value="<?php echo $password; ?>">
			</div>
			<br/>
			<input type="submit" value="<?php echo (isset($_POST['accountID']) ? 'Edit' : 'Add'); ?>!" class="btn btn-md btn-success" />
		</form>
    </body>
</html>