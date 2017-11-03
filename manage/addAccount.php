<?php
	session_start();
	$user = $_SESSION;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Add an Instagram Account</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    </head>

    <body class="text-center">
		<div class="alert alert-info text-center">
			<strong>Add an account</strong>
		</div>

		<form class="col-lg-6 col-lg-offset-3" action="action.php?action=saveAccount&user=<?php echo $user['ID']; ?>" method="POST">
			<div class="input-group">
				<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
				<input id="email" type="text" class="form-control" name="email" placeholder="Email">
			</div>
			<div class="input-group">
				<span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
				<input id="password" type="password" class="form-control" name="password" placeholder="Password">
			</div>
			<!-- <br/> -->
			<input type="submit" value="Add!" class="btn btn-md btn-success" />
		</form>
    </body>
</html>