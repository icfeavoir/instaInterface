<?php
	session_start();
	$user = $_SESSION;
	if(empty($_SESSION) || empty($_COOKIE)){
		header('Location: /');
	}
	require_once('../db.php');
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

  		<style>
  			.openModal:hover{
  				cursor: pointer;
  			}
  			th{
  				text-align: center;
  			}
  		</style>
    </head>

    <body class="text-center">
    	<div class="ajax-response"><p class=""></p></div>
		<div class="alert alert-info text-center">
			<strong>Welcome <?php echo $user['email']; ?>!</strong> Here are your accounts and some stats about it! <a href="action.php?action=logout"><button class="btn btn-warning btn-md">Logout</button></a>
		</div>
		<button class="btn btn-lg btn-success" id="newAccount">Add an account</button>
		<br/><br/>

		<table class="table table-striped table-hover">
			<tr>
				<th>Email</th>
				<th>Status</th>
				<th>More</th>
				<th>Update</th>
				<th>Delete</th>
			</tr>
			<?php
				$status = array('Nothing wrong!', 'The account has been blocked: <button id="unblock" class="btn btn-danger btn-sm">What should I do?</button>');

				$accounts = $db->prepare('SELECT * FROM Account WHERE user_id=:user_id');
				$accounts->execute(array(':user_id'=>$user['ID']));
				$accounts = $accounts->fetchAll();
				foreach ($accounts as $account) {
					?>
						<tr>
							<td><?php echo $account['email'] ?></td>
							<td><?php echo $status[$account['status']] ?></td>
							<td><a class="openModal" id="plus"><i class="fa fa-plus"></i></a></td>
							<td><a class="openModal" id="editAccount"><i class="fa fa-pencil"></i></a></td>
							<td><a class="openModal" id="delete"><i class="fa fa-trash"></i></a></td>
						</tr>
					<?php
				}
			?>
		</table>

		<!-- Modal -->
		<div class="modal fade" id="modal" role="dialog">
			<div class="modal-dialog modal-lg">
			<!-- Modal content-->
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"></h4>
					</div>
					<div class="modal-body"></div>
				</div>
			</div>
		</div>
    </body>
</html>

<script>
$(document).ready(function(){
	console.log("o");
	function showBar(isSuccess, msg){
		$('.ajax-response').css('visibility','visible').css('opacity', 1);
		$('.ajax-response p').html('<i class="fa fa-'+(isSuccess?"check":"exclamation-triangle")+'" aria-hidden="true"></i> '+msg);
		$('.ajax-response p').addClass(isSuccess?'alert-success':'alert-danger').removeClass(!isSuccess?'alert-success':'alert-danger');
		setTimeout(function(){$('.ajax-response').css('visibility','hidden').css('opacity', 0)}, 4000);
	}

	function openModal(file, data={}){
		$.post( file+".php?account=", data).done(function( resp ){
			$('#modal .modal-content .modal-title').html($(resp).filter('title').text());
			$('#modal .modal-content .modal-body').html(resp);
			$('#modal').modal();
		});
	}

	$('#newAccount').click(function(){
		openModal('addAccount');
	});

	$('.openModal').click(function(){

	});

});
</script>