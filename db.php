<?php
	$_dbUser = 'root';
    $_dbPass = 'root';
    $_dbDatabase = 'instagram';
    $_dbHost = 'localhost';
    try{
    	$db = new PDO('mysql:host='.$_dbHost.';dbname='.$_dbDatabase.';charset=utf8', $_dbUser, $_dbPass);
    }catch(Exception $e){
    	exit('Error: '.$e);
    };

    define('PATH', '/');