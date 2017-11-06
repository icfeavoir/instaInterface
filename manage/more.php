<?php
	session_start();
	require_once('../db.php');
	if(empty($_SESSION)){
		foreach ($_COOKIE as $key => $value) {
			$_SESSION[$key] = $value;
		}
	}
	if(!isset($_SESSION['ID']) || $_SESSION['ID'] == 0)
		header('Location: '.PATH.'/index.php');	
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

  		<!--Load the AJAX API-->
	    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	    <script type="text/javascript">
	    function getUrlParameter(sParam) {
		    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
		        sURLVariables = sPageURL.split('&'),
		        sParameterName,
		        i;
		    for (i = 0; i < sURLVariables.length; i++) {
		        sParameterName = sURLVariables[i].split('=');
		        if (sParameterName[0] === sParam) {
		            return sParameterName[1] === undefined ? true : sParameterName[1];
		        }
		    }
		};
		// Load the Visualization API and the corechart package.
		google.charts.load('current', {'packages':['corechart']});

		// Set a callback to run when the Google Visualization API is loaded.
		google.charts.setOnLoadCallback(drawChart);

		// Callback that creates and populates a data table,
		// instantiates the pie chart, passes in the data and
		// draws it.
	    function drawChart() {
	    	var stats = [];
	    	$.ajax({
	    		type: 'POST',
	    		url: 'action.php?action=getStats',
	    		data: {'accountID': getUrlParameter('accountID')},
	    		success: function(resp){
	    			stats = JSON.parse(resp)
	    		},
	    		async: false
	     	});
	    	console.log(stats);
			var data = google.visualization.arrayToDataTable([
				['Day', 'Number of messages sent', 'Number of messages received'],
				['2013',  1000,      400],
				['2014',  1170,      460],
				['2015',  660,       1120],
				['2016',  1030,      540]
			]);

			var options = {
				title: 'Messages',
				hAxis: {title: 'Day',  titleTextStyle: {color: '#333'}},
				vAxis: {minValue: 0}
			};

			var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
			chart.draw(data, options);
           }
	    </script>

  		<!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    </head>

    <body class="text-center">
    	<a href=<?php echo PATH.'manage/'.($_SESSION['rights']&1?'index.php':'admin.php'); ?>><button class="btn btn-md btn-primary"><i class="fa fa-arrow-left"></i> Go Back</button></a><br/><br/>
    	<?php
	    	if(isset($_GET['accountID'])){
	    		$account = $db->prepare('SELECT * FROM Account WHERE ID=:id');
	    		$account->execute(array(':id'=>$_GET['accountID']));
	    		if($account->rowCount() == 0){
	    			echo('<div class="alert alert-danger text-center">Error: this account doesn\'t exist</div>');
	    			exit;
	    		}
	    		$account = $account->fetch();
	    		if($account['user_id'] != $_SESSION['ID'] && !($_SESSION['rights']&2)){	// if your account or ADMIN
	    			echo('<div class="alert alert-danger text-center">Not your account!</div>');
	    			exit;
	    		}
	    	}else{
	    		echo('<div class="alert alert-danger text-center">Error: no account selected</div>');
	    		exit;
	    	}
	    	$email = $account['email'] ?? '';
    	?>
		<div class="alert alert-info text-center">
			<strong>More about this account: <?php echo $email; ?></strong>
		</div>

		<div id="chart_div" style="width: 100%; height: 500px;"></div>
    </body>
</html>