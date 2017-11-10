<?php
	session_start();
	require_once('../db.php');

	if(empty($_SESSION))
		exit('Not connected');
	if(!isset($_GET['action']))
		exit('No action');

	$action = $_GET['action'];

// USER

	if($action == 'saveAccount'){
		if(isset($_POST['username']) && isset($_POST['password']) && $_POST['username'] != "" && $_POST['password'] != ""){
			// no auto increment...
			$lastID = $db->prepare('SELECT account_id FROM scraping2.Account ORDER BY account_id DESC LIMIT 1');
			$lastID->execute();
			$lastID = $lastID->fetch()['account_id'];
			$statement = $db->prepare('INSERT INTO scraping2.Account (account_id, username, password, instaface_id, read_inbox, send_messages) VALUES(:newID, :username, :password, :instaface_id, true, true)');
			$statement->execute(array(':newID'=>$lastID+1, ':username'=>$_POST['username'], ':password'=>$_POST['password'], ':instaface_id'=>$_SESSION['ID']));
		}
		header('Location: index.php');
	}else if($action == 'editAccount'){
		if(isset($_GET['ID']) && isset($_POST['username']) && isset($_POST['password']) && $_POST['username'] != "" && $_POST['password'] != ""){
			$statement = $db->prepare('UPDATE scraping2.Account SET username=:username, password=:password WHERE account_id=:ID LIMIT 1');
			$statement->execute(array(':username'=>$_POST['username'], ':password'=>$_POST['password'], ':ID'=>$_GET['ID']));
		}
		header('Location: index.php');
	}else if($action == 'deleteAccount'){
		if(isset($_POST['ID'])){
			$statement = $db->prepare('DELETE FROM scraping2.Account WHERE account_id=:ID LIMIT 1');
			$statement->execute(array(':ID'=>$_POST['ID']));
		}
	}else if($action == 'getStats'){
		$json = array();
		if(isset($_POST['accountID'])){
			$statement = $db->prepare('SELECT DATE(FROM_UNIXTIME(timestamp/1000000)) as date FROM scraping2.ThreadItem WHERE thread_id IN (SELECT thread_id FROM scraping2.Thread WHERE account_id=:accountID) AND response=false ORDER BY timestamp');
			$statement->execute(array(':accountID'=>$_POST['accountID']));
			$json['sent'] = $statement->fetchAll(PDO::FETCH_ASSOC);

			$statement = $db->prepare('SELECT DATE(FROM_UNIXTIME(timestamp/1000000)) as date FROM scraping2.ThreadItem WHERE thread_id IN (SELECT thread_id FROM scraping2.Thread WHERE account_id=:accountID) AND response=true ORDER BY timestamp');
			$statement->execute(array(':accountID'=>$_POST['accountID']));
			$json['received'] = $statement->fetchAll(PDO::FETCH_ASSOC);
		}
		echo json_encode($json);
	}else if($action == 'reconnect'){
		if(isset($_POST['accountID'])){
			$statement = $db->prepare('UPDATE scraping2.Account SET status=0 WHERE account_id=:accountID');
			$statement->execute(array(':accountID'=>$_POST['accountID']));
		}
	}

// ADMIN
	else if($action == 'saveUser'){
		if(isset($_POST['email']) && isset($_POST['password']) && $_POST['email'] != "" && $_POST['password'] != ""){
			$statement = $db->prepare('INSERT INTO instagram.User (email, password, rights) VALUES(:email, :password, :rights)');
			$statement->execute(array(':email'=>$_POST['email'], ':password'=>password_hash($_POST['password'], PASSWORD_DEFAULT), ':rights'=>$_POST['rights']));
		}
		header('Location: admin.php');
	}else if($action == 'editUser'){
		if(isset($_GET['ID']) && isset($_POST['email']) && isset($_POST['password']) && $_POST['email'] != "" && $_POST['password'] != ""){
			$statement = $db->prepare('UPDATE instagram.User SET email=:email, password=:password, rights=:rights WHERE instaface_id=:ID LIMIT 1');
			$statement->execute(array(':email'=>$_POST['email'], ':password'=>password_hash($_POST['password'], PASSWORD_DEFAULT), ':rights'=>$_POST['rights'], ':ID'=>$_GET['ID']));
		}
		header('Location: admin.php');
	}else if($action == 'deleteUser'){
		if(isset($_POST['ID'])){
			$statement = $db->prepare('DELETE FROM instagram.User WHERE instaface_id=:ID LIMIT 1');
			$statement->execute(array(':ID'=>$_POST['ID']));
			$statement = $db->prepare('UPDATE scraping2.Account SET Account.instaface_id=0 WHERE Account.instaface_id=:ID');
			$statement->execute(array(':ID'=>$_POST['ID']));
		}
	}else if($action == 'getTotalNumbers'){
		$json = array();
		if(isset($_POST['userID'])){
			$statement = $db->prepare('SELECT Account.instaface_id as instaface_id, COUNT(DISTINCT scraping2.ThreadItem.thread_id) as nb FROM ((scraping2.ThreadItem INNER JOIN scraping2.Thread ON scraping2.Thread.thread_id = scraping2.ThreadItem.thread_id) INNER JOIN scraping2.Account ON scraping2.Thread.account_id = Account.account_id) WHERE scraping2.ThreadItem.response=false AND Account.instaface_id=:userID GROUP BY Account.instaface_id');
			$statement->execute(array(':userID'=>$_POST['userID']));
			$json['sent'] = $statement->fetch(PDO::FETCH_ASSOC)['nb'];

			$statement = $db->prepare('SELECT Account.instaface_id as instaface_id, COUNT(DISTINCT scraping2.ThreadItem.thread_id) as nb FROM ((scraping2.ThreadItem INNER JOIN scraping2.Thread ON scraping2.Thread.thread_id = scraping2.ThreadItem.thread_id) INNER JOIN scraping2.Account ON scraping2.Thread.account_id = Account.account_id) WHERE scraping2.ThreadItem.response=true AND Account.instaface_id=:userID GROUP BY Account.instaface_id');
			$statement->execute(array(':userID'=>$_POST['userID']));
			$json['received'] = $statement->fetch(PDO::FETCH_ASSOC)['nb'];
		}
		echo json_encode($json);
	}
	else if($action == 'getGraphUser'){
		$json = array();
		if(isset($_POST['type'])){
			switch($_POST['type']){
				case 'forever';
					$statementReceived = $db->prepare('SELECT instagram.User.email, Account.instaface_id as instaface_id, COUNT(DISTINCT scraping2.ThreadItem.thread_id) as nb FROM (((scraping2.ThreadItem INNER JOIN scraping2.Thread ON scraping2.Thread.thread_id = scraping2.ThreadItem.thread_id) INNER JOIN scraping2.Account ON scraping2.Thread.account_id = Account.account_id) INNER JOIN instagram.User ON instagram.User.instaface_id=scraping2.Account.instaface_id) WHERE scraping2.ThreadItem.response=true GROUP BY Account.instaface_id ORDER BY instagram.User.instaface_id');
					$statementSent = $db->prepare('SELECT instagram.User.email, Account.instaface_id as instaface_id, COUNT(DISTINCT scraping2.ThreadItem.thread_id) as nb FROM (((scraping2.ThreadItem INNER JOIN scraping2.Thread ON scraping2.Thread.thread_id = scraping2.ThreadItem.thread_id) INNER JOIN scraping2.Account ON scraping2.Thread.account_id = Account.account_id) INNER JOIN instagram.User ON instagram.User.instaface_id=scraping2.Account.instaface_id) WHERE scraping2.ThreadItem.response=false GROUP BY Account.instaface_id ORDER BY instagram.User.instaface_id');	
					break;
				case 'monthly';
					$statementReceived = $db->prepare('SELECT instagram.User.email, Account.instaface_id as instaface_id, COUNT(DISTINCT scraping2.ThreadItem.thread_id) as nb FROM (((scraping2.ThreadItem INNER JOIN scraping2.Thread ON scraping2.Thread.thread_id = scraping2.ThreadItem.thread_id) INNER JOIN scraping2.Account ON scraping2.Thread.account_id = Account.account_id) INNER JOIN instagram.User ON instagram.User.instaface_id=scraping2.Account.instaface_id) WHERE scraping2.ThreadItem.response=true AND (DATE(FROM_UNIXTIME(scraping2.ThreadItem.timestamp/1000000)) > NOW() - INTERVAL 1 MONTH) GROUP BY Account.instaface_id ORDER BY instagram.User.instaface_id');
					$statementSent = $db->prepare('SELECT instagram.User.email, Account.instaface_id as instaface_id, COUNT(DISTINCT scraping2.ThreadItem.thread_id) as nb FROM (((scraping2.ThreadItem INNER JOIN scraping2.Thread ON scraping2.Thread.thread_id = scraping2.ThreadItem.thread_id) INNER JOIN scraping2.Account ON scraping2.Thread.account_id = Account.account_id) INNER JOIN instagram.User ON instagram.User.instaface_id=scraping2.Account.instaface_id) WHERE scraping2.ThreadItem.response=false AND (DATE(FROM_UNIXTIME(scraping2.ThreadItem.timestamp/1000000)) > NOW() - INTERVAL 1 MONTH) GROUP BY Account.instaface_id ORDER BY instagram.User.instaface_id');
					break;
				case 'weekly';
					$statementReceived = $db->prepare('SELECT instagram.User.email, Account.instaface_id as instaface_id, COUNT(DISTINCT scraping2.ThreadItem.thread_id) as nb FROM (((scraping2.ThreadItem INNER JOIN scraping2.Thread ON scraping2.Thread.thread_id = scraping2.ThreadItem.thread_id) INNER JOIN scraping2.Account ON scraping2.Thread.account_id = Account.account_id) INNER JOIN instagram.User ON instagram.User.instaface_id=scraping2.Account.instaface_id) WHERE scraping2.ThreadItem.response=true AND (DATE(FROM_UNIXTIME(scraping2.ThreadItem.timestamp/1000000)) > NOW() - INTERVAL 1 WEEK) GROUP BY Account.instaface_id ORDER BY instagram.User.instaface_id');
					$statementSent = $db->prepare('SELECT instagram.User.email, Account.instaface_id as instaface_id, COUNT(DISTINCT scraping2.ThreadItem.thread_id) as nb FROM (((scraping2.ThreadItem INNER JOIN scraping2.Thread ON scraping2.Thread.thread_id = scraping2.ThreadItem.thread_id) INNER JOIN scraping2.Account ON scraping2.Thread.account_id = Account.account_id) INNER JOIN instagram.User ON instagram.User.instaface_id=scraping2.Account.instaface_id) WHERE scraping2.ThreadItem.response=false AND (DATE(FROM_UNIXTIME(scraping2.ThreadItem.timestamp/1000000)) > NOW() - INTERVAL 1 WEEK) GROUP BY Account.instaface_id ORDER BY instagram.User.instaface_id');
					break;

			}
			$statementReceived->execute();
			$json['received'] = $statementReceived->fetchAll(PDO::FETCH_ASSOC);
			$statementSent->execute();
			$json['sent'] = $statementSent->fetchAll(PDO::FETCH_ASSOC);
		}
		echo json_encode($json);
	}

// ALL

	else if($action == 'getTops'){
		?>
		<div>
			<div class="col-lg-4">		
				<div class="alert alert-info text-center">Weekly Top</div>				
				<table class="table">
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
				<table class="table">
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
				<table class="table">
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
		<?php
	}
	
	else if($action == 'logout'){
		session_destroy();
		setcookie('ID', 0, time()+30*24*3600, PATH, null, false, true);
		header('Location: '.PATH);
	}