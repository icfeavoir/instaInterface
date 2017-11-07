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
	if(!isset($_SESSION['ID']) || $_SESSION['ID'] == 0 || !($_SESSION['rights']&1))
		header('Location: '.PATH.'index.php');

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
  		<script src="bootbox.min.js"></script>

  		<style>
  			.openModal:hover{
  				cursor: pointer;
  			}
  			th{
  				text-align: center;
  			}
  			#accountsTable th:hover{
  				cursor: pointer;
  			}
  		</style>
    </head>

    <body class="text-center">
		<div class="alert alert-info text-center">
			<strong>Welcome <?php echo $_SESSION['email']; ?>!</strong> Here are your accounts and some stats about it! <a href="action.php?action=logout"><button class="btn btn-warning btn-md">Logout</button></a>
		</div>
		<button class="btn btn-lg btn-success" id="newAccount">Add an account</button>
		<br/><br/>

		<table class="table table-striped table-hover" id="accountsTable">
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
				$accounts->execute(array(':user_id'=>$_SESSION['ID']));
				$accounts = $accounts->fetchAll();
				foreach ($accounts as $account) {
					?>
						<tr>
							<td><?php echo $account['email'] ?></td>
							<td><?php echo $status[$account['status']] ?></td>
							<td><a href="more.php?accountID=<?php echo $account['ID']; ?>"><i class="fa fa-plus"></i></a></td>
							<td><a class="openModal" account=<?php echo $account['ID']; ?> id="edit"><i class="fa fa-pencil"></i></a></td>
							<td><a class="openModal" account=<?php echo $account['ID']; ?> id="delete"><i class="fa fa-trash"></i></a></td>
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
	function openModal(file, data={}, sync=true){
		$.ajax({
			type: 'POST',
			url: file+".php?account=",
			data: data,
			success: function( resp ){
				$('#modal .modal-content .modal-title').html($(resp).filter('title').text());
				$('#modal .modal-content .modal-body').html(resp);
				$('#modal').modal();
			},
			async: sync,	// synchronous for google charts
		});
	}

	$('#newAccount').click(function(){
		openModal('addAccount');
	});

	$('.openModal').click(function(){
		var account = $(this).attr('account');
		switch($(this).attr("id")){
			case "edit":
				openModal('addAccount', {'accountID': account});
				break;
			case "delete":
				var line = $(this).closest('tr');
				bootbox.confirm({
				    message: "Are you sure you want to delete this account?",
				    buttons: {
				        confirm: {
				            label: 'Yes',
				            className: 'btn-success'
				        },
				        cancel: {
				            label: 'No',
				            className: 'btn-danger'
				        }
				    },
				    callback: function (result) {
				    	if(result){
				    		$.post('action.php?action=deleteAccount', {'ID': account});
				    		line.remove();
				    	}
				    }
				});
				break;
		}
	});

	$('#accountsTable th').each(function(i){
		$(this).click(function(){sortTable(i)});
	});

	function sortTable(n) {
		var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
		table = document.getElementById("accountsTable");
		switching = true;
		//Set the sorting direction to ascending:
		dir = "asc"; 
		/*Make a loop that will continue until
		no switching has been done:*/
		while (switching) {
		    //start by saying: no switching is done:
		    switching = false;
		    rows = table.getElementsByTagName("TR");
		    /*Loop through all table rows (except the
		    first, which contains table headers):*/
		    for (i = 1; i < (rows.length - 1); i++) {
				//start by saying there should be no switching:
				shouldSwitch = false;
				/*Get the two elements you want to compare,
				one from current row and one from the next:*/
				x = rows[i].getElementsByTagName("TD")[n];
				y = rows[i + 1].getElementsByTagName("TD")[n];
				/*check if the two rows should switch place,
				based on the direction, asc or desc:*/
		      	if (dir == "asc") {
			      	if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
			        	  //if so, mark as a switch and break the loop:
			          	shouldSwitch= true;
			          	break;
			      	}
		  		} else if (dir == "desc") {
		  			if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
		          		//if so, mark as a switch and break the loop:
		          		shouldSwitch= true;
		          		break;
		      		}
		  		}
			}
			if (shouldSwitch) {
				/*If a switch has been marked, make the switch
				and mark that a switch has been done:*/
				rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
				switching = true;
				//Each time a switch is done, increase this count by 1:
				switchcount ++;      
			} else {
				/*If no switching has been done AND the direction is "asc",
				set the direction to "desc" and run the while loop again.*/
				if (switchcount == 0 && dir == "asc") {
					dir = "desc";
					switching = true;
	      		}
			}
		}
	}
	$('#unblock').click(function(){
		bootbox.alert({
		    message: "You should connect to this Instagram account, and check the <i>I'm not a robot</i> field.",
		    backdrop: true
		});
	})

});
</script>