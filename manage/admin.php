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
	if(!isset($_SESSION['ID']) || $_SESSION['ID'] == 0 || !($_SESSION['rights']&2))
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

  		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

  		<style>
  			.openModal:hover{
  				cursor: pointer;
  			}
  			th{
  				text-align: center;
  			}
  			th:hover{
  				cursor: pointer;
  			}
  		</style>
    </head>

    <body class="text-center">
		<div class="alert alert-info text-center">
			<strong>Welcome <?php echo $_SESSION['email']; ?>!</strong> As an admin you can add users and see some stats! <a href="action.php?action=logout"><button class="btn btn-warning btn-md">Logout</button></a>
		</div>
		<button class="btn btn-lg btn-success" id="newAccount">Add an user</button>
		<br/><br/>

		<div class="col-lg-12 toLoad" id="getTops">Loading tops... <i class="fa fa-circle-o-notch fa-spin"></i></div>
		<br/><br/>

		<div class="col-lg-1">
			<div class="radio">
			  <label><input type="radio" name="graphbtn" class="graphRadioBtn" id="weekly">Weekly</label>
			</div><br/>
			<div class="radio">
			  <label><input type="radio" name="graphbtn" class="graphRadioBtn" id="monthly">Monthly</label>
			</div><br/>
			<div class="radio">
			  <label><input type="radio" name="graphbtn" class="graphRadioBtn" id="forever" checked>Forever</label>
			</div><br/>
		</div>
		<div class="col-lg-11 text-center">
			<div id="load_graph">Loading graph... <i class="fa fa-circle-o-notch fa-spin"></i></div>
			<div id="users_stats"></div>
		</div>

		<div class="alert alert-info text-center col-sm-12">Users</div>
		<table class="table table-striped table-hover" id="externalTable">
			<tr>
				<th>Email</th>
				<th>Number of accounts</th>
				<th>Total number conv. started</th>
				<th>Total number conv. replied</th>
				<th>More</th>
				<th>Update</th>
				<th>Delete</th>
			</tr>
			<?php
				$users = $db->prepare('SELECT * FROM instagram.User WHERE rights=1 ORDER BY instaface_id DESC');
				$users->execute();
				$users = $users->fetchAll();
				foreach ($users as $user) {
					$accounts = $db->prepare('SELECT COUNT(*) as nb FROM scraping2.Account WHERE instaface_id=:instaface_id');
					$accounts->execute(array(':instaface_id'=>$user['instaface_id']));
					$nbAccounts = $accounts->fetch()['nb'];
					?>
						<tr>
							<td class="userEmail"><?php echo $user['email']; ?></td>
							<td><?php echo $nbAccounts; ?></td>
							<td class="msgSent" id="<?php echo $user['instaface_id']; ?>"><i class="fa fa-circle-o-notch fa-spin"></i></td>
							<td class="msgReceived" id="<?php echo $user['instaface_id']; ?>"><i class="fa fa-circle-o-notch fa-spin"></i></td>
							<td><a class="openModal" user=<?php echo $user['instaface_id']; ?> id="more"><i class="fa fa-plus"></i></a></td>
							<td><a class="openModal" user=<?php echo $user['instaface_id']; ?> id="edit"><i class="fa fa-pencil"></i></a></td>
							<td><a class="openModal" user=<?php echo $user['instaface_id']; ?> id="delete"><i class="fa fa-trash"></i></a>
						</tr>
					<?php
				}
			?>
		</table>

		<div class="alert alert-info text-center">All accounts with conversations</div>
		<table class="table table-striped table-hover" id="allAccountsTable">
			<tr>
				<th>Owner</th>
				<th>Username</th>
				<th>Conversation started</th>
				<th>Conversation with at least 1 reply</th>
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
						<tr">
							<td><?php echo $db->query('SELECT email FROM instagram.User WHERE instaface_id='.$account['instaface_id'])->fetch()['email']; ?></td>
							<td><?php echo $account['username'] ?></td>
							<td><?php echo $started ?></td>
							<td><?php echo $replied ?></td>
							<td><?php echo $started != 0 ? round($replied*100/$started, 2) : 0 ?></td>
						</tr>
					<?php
				}
			?>
		</table>
		
		<div class="alert alert-info text-center col-sm-12">Admin (You)</div>
		<table class="table table-striped table-hover">
			<tr>
				<th>Email</th>
				<th>Update</th>
			</tr>
			<?php
				$users = $db->prepare('SELECT * FROM instagram.User WHERE rights=2 ORDER BY instaface_id DESC');
				$users->execute();
				$users = $users->fetchAll();
				foreach ($users as $user) {
					?>
						<tr>
							<td><?php echo $user['email']; ?></td>
							<td><a class="openModal" user=<?php echo $user['instaface_id']; ?> id="edit"><i class="fa fa-pencil"></i></a></td>
						</tr>
					<?php
				}
			?>
		</table>

		<div class="modal fade" id="modal" role="dialog">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"></h4>
					</div>
					<div class="modal-body">Please wait... <i class="fa fa-circle-o-notch fa-spin"></i></div>
				</div>
			</div>
		</div>
    </body>
</html>

<script>
$(document).ready(function(){
	google.charts.load('current', {packages: ['corechart', 'bar']});
	google.charts.setOnLoadCallback(function(){drawGraph()});
	var graph, materialChart;

	function openModal(file, data={}, sync=true){
		$('#modal').modal();
		$('.modal-body').html('Please wait... <i class="fa fa-circle-o-notch fa-spin">');
		$.ajax({
			type: 'POST',
			url: file+".php",
			data: data,
			success: function( resp ){
				$('.modal-title').html($(resp).filter('title').text());
				$('.modal-body').html(resp);
			},
			async: true,
		});
	}

	$('#newAccount').click(function(){
		openModal('addUser');
	});

	$('.openModal').click(function(){
		var user = $(this).attr('user');
		switch($(this).attr("id")){
			case "more":
				openModal('user', {'userID': user});
				break;
			case "edit":
				openModal('addUser', {'userID': user});
				break;
			case "delete":
				var line = $(this).closest('tr');
				bootbox.confirm({
				    message: "Are you sure you want to delete this user?",
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
				    		$.post('action.php?action=deleteUser', {'ID': user});
				    		line.remove();
				    	}
				    }
				});
				break;
		}
	});

	$('.graphRadioBtn').click(function(){
		$('#load_graph').html('Loading graph... <i class="fa fa-circle-o-notch fa-spin"></i>');
		$('#users_stats').html('');
		graph = new google.visualization.DataTable();
		graph.addColumn('string', 'User');
		graph.addColumn('number', 'Conversation started');
		graph.addColumn('number', 'Conversation with reply');
		// $('#users_stats').html('<i class="fa fa-circle-o-notch fa-spin"></i>');
		$.post('action.php?action=getGraphUser', {'type': $(this).attr('id')}).done(function(resp){
			resp = JSON.parse(resp);
			for(var i=0; i<resp.received.length; i++){
				graph.addRow([resp.received[i].email, parseInt(resp.sent[i].nb), parseInt(resp.received[i].nb)]);
			}
			var materialChart = new google.charts.Bar(document.getElementById('users_stats'));
			$('#load_graph').html('');
			materialChart.draw(graph);
		});
	});

	function drawGraph(){
		graph = new google.visualization.DataTable();
		materialChart = new google.charts.Bar(document.getElementById('users_stats'));
		graph.addColumn('string', 'User');
		graph.addColumn('number', 'Conversation started');
		graph.addColumn('number', 'Conversation with reply');
		
		$('td.msgSent').each(function(i){	// get all users and fill the table
			var it = $(this);
			$.ajax({
				type: 'POST',
				url: "action.php?action=getTotalNumbers",
				data: {userID: $(this).attr("id")},
				success: function( resp ){
					resp = JSON.parse(resp);
					resp.sent = resp.sent || 0;
					resp.received = resp.received || 0;
					it.text(resp.sent);
					it.next().text(resp.received);

					if(parseInt(resp.sent) != 0){
						graph.addRow([it.closest("tr").find(".userEmail").text(), parseInt(resp.sent), parseInt(resp.received)]);
						$('#load_graph').html('');
						materialChart.draw(graph);
					}
				},
				async: true,
			});
		});
	}

	$('#externalTable th').each(function(i){
		$(this).click(function(){sortTable('externalTable', i)});
	});
	$('#allAccountsTable th').each(function(i){
		$(this).click(function(){sortTable('allAccountsTable', i)});
	});

	$('.toLoad').each(function(i){
		var div = $(this);
		$.post('action.php?action='+div.attr("ID")).done(function(resp){
			div.html(resp);
		});
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
		      		if(Number.isInteger(parseInt(x.innerHTML))){
		      			if(parseInt(x.innerHTML) > parseInt(y.innerHTML)){
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
		  			if(Number.isInteger(parseInt(x.innerHTML))){
						if(parseInt(x.innerHTML) < parseInt(y.innerHTML)){
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

	//sort table by percentage
	sortTable('allAccountsTable', 4);
});
</script>