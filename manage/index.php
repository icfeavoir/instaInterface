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
  			#accountsTable, #othersAccountsTable th:hover{
  				cursor: pointer;
  			}
  			.specialLine{
  				background-color: #AEC7E8;
  			}
  			.ajax-response{
				position: fixed;
				z-index: 1;
				width: 50%;
				border-radius: 10px;
				left: 25%;
				text-align: center;
				visibility: hidden;
				opacity: 0;
				-webkit-transition: visibility 1s, opacity 1s;
				transition: visibility 1s, opacity 1s;
			}
  		</style>
    </head>

    <body class="text-center">
    	<div class="ajax-response">
    		<p class="alert"></p>
    	</div>

		<div class="alert alert-info text-center">
			<strong>Welcome <?php echo $_SESSION['email']; ?>!</strong> Here are your accounts and some stats about it! <a href="action.php?action=logout"><button class="btn btn-warning btn-md">Logout</button></a>
		</div>
		<button class="btn btn-lg btn-success" id="newAccount">Add an account</button>
		<br/><br/>

		<div class="col-lg-12 toLoad" id="getTops">Loading tops... <i class="fa fa-circle-o-notch fa-spin"></i></div>
		<br/><br/>

		<div class="alert alert-info text-center col-lg-12">Your account(s)</div>
		<table class="table table-striped table-hover" id="accountsTable">
			<tr>
				<th>Username</th>
				<th>Status</th>
				<th>Follow</th>
				<th>Like</th>
				<th>Chat</th>
				<th>Conversations started</th>
				<th>Conversations with at least 1 reply</th>
				<th>% of reply</th>
				<th>Update</th>
				<th>Delete</th>
			</tr>
			<?php
				$accounts = $db->prepare('SELECT * FROM scraping2.Account WHERE instaface_id=:instaface_id');
				$accounts->execute(array(':instaface_id'=>$_SESSION['ID']));
				$accounts = $accounts->fetchAll();

				foreach ($accounts as $account) {
					$status = array(
								0=>'Everything is fine!',
								1=>'The account has been blocked: <button id="'.$account['account_id'].'" msg="1" class="unblock btn btn-danger btn-sm">What should I do?</button>',
								2=>'Your username or password is wrong. <button id="edit" account="'.$account['account_id'].'" class="btn btn-warning btn-sm openModal">Change it!</button>',
								3=>'Unknown error... <button id="'.$account['account_id'].'" msg="3" class="unblock btn btn-danger btn-sm">What should I do?</button>',
							);

					$started = $db->query('SELECT COUNT(DISTINCT thread_id) as nb FROM scraping2.ThreadItem WHERE thread_id IN (SELECT thread_id FROM scraping2.Thread WHERE account_id='.$account['account_id'].') AND response=false')->fetch()['nb'];
					$replied = $db->query('SELECT COUNT(DISTINCT thread_id) as nb FROM scraping2.ThreadItem WHERE thread_id IN (SELECT thread_id FROM scraping2.Thread WHERE account_id='.$account['account_id'].') AND response=true')->fetch()['nb'];
					$follow = $db->query('SELECT COUNT(*) AS nb FROM scraping2.AccountFollow WHERE account_id='.$account['account_id'])->fetch()['nb'];
					$like = $db->query('SELECT COUNT(*) AS nb FROM scraping2.AccountLove WHERE account_id='.$account['account_id'])->fetch()['nb'];
					?>
						<tr>
							<td><?php echo $account['username'] ?></td>
							<td><?php echo $status[$account['status']] ?></td>
							<td><div class="checkbox"><label><input id="<?php echo $account['account_id']; ?>" type="checkbox" class="action" field="follow" <?php echo $follow ? 'checked' : '' ?> ></label></div></td>
							<td><div class="checkbox"><label><input id="<?php echo $account['account_id']; ?>" type="checkbox" class="action" field="like" <?php echo $like ? 'checked' : '' ?> ></label></div></td>
							<td><div class="checkbox"><label><input id="<?php echo $account['account_id']; ?>" type="checkbox" class="action" field="chat" <?php echo $account['send_messages'] ? 'checked' : '' ?> ></label></div></td>
							<td><?php echo $started ?></td>
							<td><?php echo $replied ?></td>
							<td><?php echo $started != 0 ? round($replied*100/$started, 2) : 0 ?></td>
							<td><a class="openModal" account=<?php echo $account['account_id']; ?> id="edit"><i class="fa fa-pencil"></i></a></td>
							<td><a class="openModal" account=<?php echo $account['account_id']; ?> id="delete"><i class="fa fa-trash"></i></a></td>
						</tr>
					<?php
				}
			?>
		</table>

		<div class="alert alert-info text-center">All accounts with conversations</div>
		<table class="table" id="othersAccountsTable">
			<tr>
				<th>Owner</th>
				<th>Username</th>
				<th>Conversations started</th>
				<th>Conversations with at least 1 reply</th>
				<th>% of reply</th>
			</tr>
			<?php
				$accounts = $db->query('SELECT * FROM scraping2.Account WHERE instaface_id != 0 ORDER BY scraping2.Account.instaface_id');
				$accounts = $accounts->fetchAll();

				foreach ($accounts as $account) {
					$started = $db->query('SELECT COUNT(DISTINCT thread_id) as nb FROM scraping2.ThreadItem WHERE thread_id IN (SELECT thread_id FROM scraping2.Thread WHERE account_id='.$account['account_id'].') AND response=false')->fetch()['nb'];
					$replied = $db->query('SELECT COUNT(DISTINCT thread_id) as nb FROM scraping2.ThreadItem WHERE thread_id IN (SELECT thread_id FROM scraping2.Thread WHERE account_id='.$account['account_id'].') AND response=true')->fetch()['nb'];
					if($started == 0 || $replied < 50)
						continue;
					?>
						<tr class="<?php echo $account['instaface_id'] == $_SESSION['ID'] ? 'specialLine' : '' ?>">
							<td><?php echo $account['instaface_id'] == $_SESSION['ID'] ? 'You' : $db->query('SELECT email FROM instagram.User WHERE instaface_id='.$account['instaface_id'])->fetch()['email']; ?></td>
							<td><?php echo $account['username'] ?></td>
							<td><?php echo $started ?></td>
							<td><?php echo $replied ?></td>
							<td><?php echo $started != 0 ? round($replied*100/$started, 2) : 0 ?></td>
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
			url: file+".php",
			data: data,
			success: function( resp ){
				$('#modal .modal-content .modal-title').html($(resp).filter('title').text());
				$('#modal .modal-content .modal-body').html(resp);
				$('#modal').modal();
			},
			async: sync,
		});
	}

	function showBar(isSuccess, msg){
		$('.ajax-response').css('visibility','visible').css('opacity', 1);
		$('.ajax-response p').html('<i class="fa fa-'+(isSuccess?"check":"exclamation-triangle")+'" aria-hidden="true"></i> '+msg);
		$('.ajax-response p').addClass(isSuccess?'alert-success':'alert-danger').removeClass(!isSuccess?'alert-success':'alert-danger');
		setTimeout(function(){$('.ajax-response').css('visibility','hidden').css('opacity', 0)}, 4000);
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
				            className: 'btn-danger'
				        },
				        cancel: {
				            label: 'No',
				            className: 'btn-success'
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

	$('.toLoad').each(function(i){
		var div = $(this);
		$.post('action.php?action='+div.attr("ID")).done(function(resp){
			div.html(resp);
		});
	});

	$('#accountsTable th').each(function(i){
		$(this).click(function(){sortTable("accountsTable", i)});
	});
	$('#othersAccountsTable th').each(function(i){
		$(this).click(function(){sortTable("othersAccountsTable", i)});
	});

	function sortTable(table, n) {
		var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
		table = document.getElementById(table);
		switching = true;
		//Set the sorting direction to ascending:
		dir = "desc"; 
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
		      		if(Number.isInteger(parseFloat(x.innerHTML))){
		      			if(parseFloat(x.innerHTML) > parseFloat(y.innerHTML)){
		      				shouldSwitch = true;
		      				break;
		      			}
		      		}else{
		      			if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
				        	  //if so, mark as a switch and break the loop:
				          	shouldSwitch= true;
				          	break;
				      	}
		      		}
		  		} else if (dir == "desc") {
		  			if(Number.isInteger(parseFloat(x.innerHTML))){
						if(parseFloat(x.innerHTML) < parseFloat(y.innerHTML)){
		      				shouldSwitch = true;
		      				break;
		      			}
		      		}else{
			  			if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
			          		//if so, mark as a switch and break the loop:
			          		shouldSwitch= true;
			          		break;
			      		}
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
				if (switchcount == 0 && dir == "desc") {
					dir = "asc";
					switching = true;
	      		}
			}
		}
	}
	$('.unblock').click(function(){
		var accountID = $(this);
		var msg = parseInt($(this).attr('msg'));
		switch(msg){
			case 1:
				bootbox.confirm({
				    message: "You should connect to this Instagram account, and check the <i>I'm not a robot</i> field. Is it done?",
				    backdrop: true,
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
				    		$.post('action.php?action=reconnect', {'accountID': accountID.attr('id')});
				    		accountID.parent().text('Will try to reconnect');
				    	}
				    }
				});
				break;

			case 3:
				bootbox.confirm({
				    message: "You should connect to this Instagram account, and see if everything is alright (your account is not blocked). Is it done?",
				    backdrop: true,
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
				    		$.post('action.php?action=reconnect', {'accountID': accountID.attr('id')});
				    		accountID.parent().text('Will try to reconnect');
				    	}
				    }
				});
				break;
		}
	});

	$('.action').click(function(){
		$.post('action.php?action=changeState', {'accountID': $(this).attr('id'), 'field': $(this).attr('field'), 'value': $(this).prop('checked')?1:0});
		showBar(true, 'The field \"'+$(this).attr('field')+'\" has been updated');
	});

	sortTable('othersAccountsTable', 4);
});
</script>