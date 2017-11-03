<?php
	session_start();
	if(empty($_SESSION))
		exit('Not connected');
	if(isset($_GET['action']))
		exit('No action');

	$action = $_GET['action'];

	if($action == 'saveAccount'){
		if(isset($_POST['email']) && isset($_POST['password'])){
			
		}
	}