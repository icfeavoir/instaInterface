<?php
	session_start();
	require_once('../db.php');

	if(empty($_SESSION)){
		$accounts = $db->prepare('SELECT * FROM User WHERE user_id=:user_id');
		$accounts->execute(array(':user_id'=>$_COOKIE['ID']));
		$accounts = $accounts->fetchAll(PDO::FETCH_ASSOC);
		foreach ($accounts as $key => $value) {
			$_SESSION[$key] = $value;
		}
	}
	if(!isset($_SESSION['ID']) || $_SESSION['ID'] == 0 || !$_SESSION['ID']&2)
		header('Location: /index.php');
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
    			$account = $db->prepare('SELECT * FROM User WHERE ID=:id');
    			$account->execute(array(':id'=>$_POST['userID']));
    			$account = $account->fetch();
    		}else{
    			$account = array();
    		}
    		$email = $account['email'] ?? '';
    		$password = $account['password'] ?? '';
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
				<input required id="password" type="text" class="form-control" name="password" placeholder="Password" value="<?php echo $password; ?>">
			</div><br/>
			<div class="form-group">
	      		<div class="col-sm-4">
	        		<select class="form-control" name="rights">
	          			<option value="1">External</option>
	          			<option value="2">Internal (works at YouPic)</option>
	        		</select>
	      		</div>
	    	</div>
			<br/>
			<input type="submit" value="<?php echo (isset($_POST['userID']) ? 'Edit' : 'Add'); ?>!" class="btn btn-md btn-success" />
		</form>
    </body>
</html>