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
  			th{
  				text-align: center;
  			}
  			.specialLine{
  				background-color: #AEC7E8;
  			}
  		</style>
    </head>

    <body class="text-center">
		<div class="col-lg-4">		
			<div class="alert alert-info text-center">Weekly Top</div>				
			<table class="table" id="accountsTable">
				<tr>
					<th>Rank</th>
					<th>User</th>
					<th>Nb of conversations with reply</th>
				</tr>
				<?php
					$accounts = $db->query('SELECT instagram.User.email as user, instagram.User.instaface_id as instaface_id, COUNT(DISTINCT scraping2.ThreadItem.thread_id) as nb FROM (((scraping2.ThreadItem INNER JOIN scraping2.Thread ON scraping2.Thread.thread_id = scraping2.ThreadItem.thread_id) INNER JOIN scraping2.Account ON scraping2.Thread.account_id = Account.account_id) INNER JOIN instagram.User ON Account.instaface_id = instagram.User.instaface_id) WHERE scraping2.ThreadItem.response=true AND (DATE(FROM_UNIXTIME(scraping2.ThreadItem.timestamp/1000000)) > NOW()-INTERVAL 1 WEEK) GROUP BY Account.instaface_id ORDER BY COUNT(DISTINCT scraping2.ThreadItem.thread_id) DESC');
					$accounts = $accounts->fetchAll();

					foreach ($accounts as $rank=>$account) {
						?>
							<tr class="<?php echo $account['instaface_id']==$_SESSION['ID'] ? 'specialLine' : ''; ?>">
								<td><?php echo $rank+1 ?></td>
								<td><?php echo $account['instaface_id']==$_SESSION['ID'] ? 'You' : $account['user'] ?></td>
								<td><?php echo $account['nb'] ?></td>
							</tr>
						<?php
					}
				?>
			</table>
		</div>

		<div class="col-lg-4">		
			<div class="alert alert-info text-center">Monthly Top</div>				
			<table class="table" id="accountsTable">
				<tr>
					<th>Rank</th>
					<th>User</th>
					<th>Nb of conversations with reply</th>
				</tr>
				<?php
					$accounts = $db->query('SELECT instagram.User.email as user, instagram.User.instaface_id as instaface_id, COUNT(DISTINCT scraping2.ThreadItem.thread_id) as nb FROM (((scraping2.ThreadItem INNER JOIN scraping2.Thread ON scraping2.Thread.thread_id = scraping2.ThreadItem.thread_id) INNER JOIN scraping2.Account ON scraping2.Thread.account_id = Account.account_id) INNER JOIN instagram.User ON Account.instaface_id = instagram.User.instaface_id) WHERE scraping2.ThreadItem.response=true AND (DATE(FROM_UNIXTIME(scraping2.ThreadItem.timestamp/1000000)) > NOW()-INTERVAL 1 MONTH) GROUP BY Account.instaface_id ORDER BY COUNT(DISTINCT scraping2.ThreadItem.thread_id) DESC');
					$accounts = $accounts->fetchAll();

					foreach ($accounts as $rank=>$account) {
						?>
							<tr class="<?php echo $account['instaface_id']==$_SESSION['ID'] ? 'specialLine' : ''; ?>">
								<td><?php echo $rank+1 ?></td>
								<td><?php echo $account['instaface_id']==$_SESSION['ID'] ? 'You' : $account['user'] ?></td>
								<td><?php echo $account['nb'] ?></td>
							</tr>
						<?php
					}
				?>
			</table>
		</div>

		<div class="col-lg-4">
			<div class="alert alert-info text-center">Forever Top</div>			
			<table class="table" id="accountsTable">
				<tr>
					<th>Rank</th>
					<th>User</th>
					<th>Nb of conversations with reply</th>
				</tr>
				<?php
					$accounts = $db->query('SELECT instagram.User.email as user, instagram.User.instaface_id as instaface_id, COUNT(DISTINCT scraping2.ThreadItem.thread_id) as nb FROM (((scraping2.ThreadItem INNER JOIN scraping2.Thread ON scraping2.Thread.thread_id = scraping2.ThreadItem.thread_id) INNER JOIN scraping2.Account ON scraping2.Thread.account_id = Account.account_id) INNER JOIN instagram.User ON Account.instaface_id = instagram.User.instaface_id) WHERE scraping2.ThreadItem.response=true GROUP BY Account.instaface_id ORDER BY COUNT(DISTINCT scraping2.ThreadItem.thread_id) DESC');
					$accounts = $accounts->fetchAll();

					foreach ($accounts as $rank=>$account) {
						?>
							<tr class="<?php echo $account['instaface_id']==$_SESSION['ID'] ? 'specialLine' : ''; ?>">
								<td><?php echo $rank+1 ?></td>
								<td><?php echo $account['instaface_id']==$_SESSION['ID'] ? 'You' : $account['user'] ?></td>
								<td><?php echo $account['nb'] ?></td>
							</tr>
						<?php
					}
				?>
			</table>
		</div>

</html>