<?php
	session_start();
	if(!isset($_GET['err']) && !empty($_COOKIE) && isset($_COOKIE['ID']) && $_COOKIE['ID'] > 0){
		foreach ($_COOKIE as $key => $value) {
			$_SESSION[$key] = $value;
		}
		header('Location: postConnection.php');
	}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Instagram</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    </head>

    <body>
		<div class="alert alert-info text-center">
			<strong>Welcome!</strong> You are in the Instagram Account's Manager.
		</div>
		<br/>
		<div class="text-center">
    		<form class="col-lg-6 col-lg-offset-3" action="postConnection.php" method="POST">
				<div class="input-group">
					<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
					<input id="email" type="text" class="form-control" name="email" placeholder="Email">
				</div>
				<div class="input-group">
					<span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
					<input id="password" type="password" class="form-control" name="password" placeholder="Password">
				</div>
				<!-- <br/> -->
				<input type="submit" value="Connect!" class="btn btn-md btn-success" />
    		</form>
    	</div>
    	<div class="col-lg-12 text-center">
	    	<?php
	    		$err = array('Wrong email.', 'Wrong password');
	    		echo isset($_GET['err']) ? '<div class="alert alert-danger">'.$err[intval($_GET['err'])].'</div>' : '';
	    	?>
	    </div>

    </body>
</html>