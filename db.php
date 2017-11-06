<?php
	$_dbUser = 'instaface';
    $_dbPass = 'barbapappa';
    $_dbDatabase = 'instagram';
    $_dbHost = '127.0.0.1';
    try{
    	$db = new PDO('mysql:host='.$_dbHost.';dbname='.$_dbDatabase.';charset=utf8;port=7000', $_dbUser, $_dbPass);
    }catch(Exception $e){
    	exit('Error: '.$e);
    };

    define('PATH', '/');