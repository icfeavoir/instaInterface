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
        <title>User's account(s)</title>
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
    	<?php
    		if(!isset($_POST['userID'])){
    			echo('<div class="alert alert-danger text-center">Error: no user selected</div>');
	    		exit;
    		}
    	?>

		<table class="table table-striped table-hover" id="accountsTable">
			<tr>
				<th>Owner</th>
				<th>Username</th>
				<th>Conversation started</th>
				<th>Conversation with at least 1 reply</th>
				<th>% of reply</th>
			</tr>
			<?php
				$accounts = $db->prepare('SELECT * FROM scraping2.Account WHERE instaface_id = :userID');
				$accounts->execute(array(':userID'=>$_POST['userID']));
				$accounts = $accounts->fetchAll();

				foreach ($accounts as $account) {
					$started = $db->query('SELECT COUNT(DISTINCT thread_id) as nb FROM scraping2.ThreadItem WHERE thread_id IN (SELECT thread_id FROM scraping2.Thread WHERE account_id='.$account['account_id'].') AND response=false')->fetch()['nb'];
					$replied = $db->query('SELECT COUNT(DISTINCT thread_id) as nb FROM scraping2.ThreadItem WHERE thread_id IN (SELECT thread_id FROM scraping2.Thread WHERE account_id='.$account['account_id'].') AND response=true')->fetch()['nb'];
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
		<?php
		if(sizeof($accounts) == 0)
			echo '<div class="alert alert-info">This user hasn\'t any accounts yet.</div>';
		?>
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
				if (switchcount == 0 && dir == "asc") {
					dir = "desc";
					switching = true;
	      		}
			}
		}
	}

	$('#unblock').click(function(){
		bootbox.alert({
		    message: "The account's owner should connect to this Instagram account, and check the <i>I'm not a robot</i> field.",
		    backdrop: true
		});
	})
});
</script>