<?php
	session_start();
	if(empty($_SESSION)){
		foreach ($_COOKIE as $key => $value) {
			$_SESSION[$key] = $value;
		}
	}
	if(!isset($_SESSION['ID']) || $_SESSION['ID'] == 0)
		header('Location: /index.php');

	require_once('../db.php');
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>More</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    </head>

    <body class="text-center">

    	<?php
	    	if(isset($_POST['accountID'])){
	    		$account = $db->prepare('SELECT * FROM Account WHERE ID=:id');
	    		$account->execute(array(':id'=>$_POST['accountID']));
	    		if($account->rowCount() == 0){
	    			exit('Error: this account doesn\'t exist');
	    		}
	    		$account = $account->fetch();
	    	}else{
	    		exit('Error: no account selected');
	    	}
	    	$email = $account['email'] ?? '';
    	?>
		<div class="alert alert-info text-center">
			<strong>More about this account: <?php echo $email; ?></strong>
		</div>
    </body>
</html>