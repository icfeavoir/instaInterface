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
	if(!isset($_SESSION['ID']) || $_SESSION['ID'] == 0)
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
  		</style>
    </head>

    <body class="text-center">
		<div class="col-lg-4">		
			<div class="alert alert-info text-center">Weekly Top</div>				
			<table class="table table-striped table-hover" id="accountsTable">
				<tr>
					<th>Rank</th>
					<th>User</th>
					<th>Nb of conversations with reply</th>
				</tr>
				<?php
					$accounts = $db->query('SELECT instagram.User.email as user, COUNT(DISTINCT scraping2.ThreadItem.thread_id) as nb FROM (((scraping2.ThreadItem INNER JOIN scraping2.Thread ON scraping2.Thread.thread_id = scraping2.ThreadItem.thread_id) INNER JOIN scraping2.Account ON scraping2.Thread.account_id = Account.account_id) INNER JOIN instagram.User ON Account.instaface_id = instagram.User.instaface_id) WHERE scraping2.ThreadItem.response=true AND (DATE(FROM_UNIXTIME(scraping2.ThreadItem.timestamp/1000000)) > NOW()-INTERVAL 1 WEEK) GROUP BY Account.instaface_id ORDER BY COUNT(DISTINCT scraping2.ThreadItem.thread_id) DESC');
					$accounts = $accounts->fetchAll();

					foreach ($accounts as $rank=>$account) {
						?>
							<tr>
								<td><?php echo $rank+1 ?></td>
								<td><?php echo $account['user'] ?></td>
								<td><?php echo $account['nb'] ?></td>
							</tr>
						<?php
					}
				?>
			</table>
		</div>

		<div class="col-lg-4">		
			<div class="alert alert-info text-center">Monthly Top</div>				
			<table class="table table-striped table-hover" id="accountsTable">
				<tr>
					<th>Rank</th>
					<th>User</th>
					<th>Nb of conversations with reply</th>
				</tr>
				<?php
					$accounts = $db->query('SELECT instagram.User.email as user, COUNT(DISTINCT scraping2.ThreadItem.thread_id) as nb FROM (((scraping2.ThreadItem INNER JOIN scraping2.Thread ON scraping2.Thread.thread_id = scraping2.ThreadItem.thread_id) INNER JOIN scraping2.Account ON scraping2.Thread.account_id = Account.account_id) INNER JOIN instagram.User ON Account.instaface_id = instagram.User.instaface_id) WHERE scraping2.ThreadItem.response=true AND (DATE(FROM_UNIXTIME(scraping2.ThreadItem.timestamp/1000000)) > NOW()-INTERVAL 1 MONTH) GROUP BY Account.instaface_id ORDER BY COUNT(DISTINCT scraping2.ThreadItem.thread_id) DESC');
					$accounts = $accounts->fetchAll();

					foreach ($accounts as $rank=>$account) {
						?>
							<tr>
								<td><?php echo $rank+1 ?></td>
								<td><?php echo $account['user'] ?></td>
								<td><?php echo $account['nb'] ?></td>
							</tr>
						<?php
					}
				?>
			</table>
		</div>

		<div class="col-lg-4">
			<div class="alert alert-info text-center">Forever Top</div>			
			<table class="table table-striped table-hover" id="accountsTable">
				<tr>
					<th>Rank</th>
					<th>User</th>
					<th>Nb of conversations with reply</th>
				</tr>
				<?php
					$accounts = $db->query('SELECT instagram.User.email as user, COUNT(DISTINCT scraping2.ThreadItem.thread_id) as nb FROM (((scraping2.ThreadItem INNER JOIN scraping2.Thread ON scraping2.Thread.thread_id = scraping2.ThreadItem.thread_id) INNER JOIN scraping2.Account ON scraping2.Thread.account_id = Account.account_id) INNER JOIN instagram.User ON Account.instaface_id = instagram.User.instaface_id) WHERE scraping2.ThreadItem.response=true GROUP BY Account.instaface_id ORDER BY COUNT(DISTINCT scraping2.ThreadItem.thread_id) DESC');
					$accounts = $accounts->fetchAll();

					foreach ($accounts as $rank=>$account) {
						?>
							<tr>
								<td><?php echo $rank+1 ?></td>
								<td><?php echo $account['user'] ?></td>
								<td><?php echo $account['nb'] ?></td>
							</tr>
						<?php
					}
				?>
			</table>
		</div>

</html>

<script>
$(document).ready(function(){
	function sortTable(table, n) {
		var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
		table = document.getElementById(table);
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
				if (switchcount == 0 && dir == "asc") {
					dir = "desc";
					switching = true;
	      		}
			}
		}
	}

});
</script>