<?php
	session_start();
	require_once('../db.php');

	if(empty($_SESSION)){
		$accounts = $db->prepare('SELECT * FROM instagram.User WHERE instaface_id=:instaface_id');
		$accounts->execute(array(':instaface_id'=>$_COOKIE['ID']));
		$accounts = $accounts->fetchAll(PDO::FETCH_ASSOC);
		foreach ($accounts as $key => $value) {
			$_SESSION[$key] = $value;
		}
	}
	if(!isset($_SESSION['ID']) || $_SESSION['ID'] == 0 || !$_SESSION['ID']&2)
		header('Location: '.PATH.'index.php');
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title><?php echo (isset($_POST['userID']) ? 'Edit' : 'Add'); ?> an User</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    </head>

    <body class="text-center">
    	<?php
    		// edit or add
    		if(isset($_POST['userID'])){
    			$account = $db->prepare('SELECT * FROM instagram.User WHERE instaface_id=:id');
    			$account->execute(array(':id'=>$_POST['userID']));
    			$account = $account->fetch();
    		}else{
    			$account = array();
    		}
    		$email = $account['email'] ?? '';
    		$rights = $account['rights'] ?? 0;
    	?>
		<div class="alert alert-info text-center">
			<strong><?php echo (isset($_POST['userID']) ? 'Edit' : 'Add'); ?> an User</strong>
		</div>

		<form action="action.php?action=<?php echo (isset($_POST['userID']) ? 'editUser&ID='.$_POST['userID'] : 'saveUser'); ?>" method="POST">
			<div class="input-group">
				<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
				<input required id="email" type="text" class="form-control" name="email" placeholder="Email" value="<?php echo $email; ?>">
			</div>
			<div class="input-group">
				<span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
				<input required id="password" type="text" class="form-control" name="password" placeholder="Password">
			</div><br/>
			
	    	<input type="hidden" name="rights" value="1" />
			<br/>
			<input type="submit" value="<?php echo (isset($_POST['userID']) ? 'Edit' : 'Add'); ?>!" class="btn btn-md btn-success" />
		</form>
    </body>
</html>