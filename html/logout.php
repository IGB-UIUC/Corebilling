<?php
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	
	include('includes/initializer.php');
    $authenticate->Logout();
    
    header('location:login.php');
?>
