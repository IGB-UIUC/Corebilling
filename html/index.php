<?php
include('includes/initializer.php');

$pages = new Pages($sqlDataBase);
$pagelist = $pages->GetPagesList();
$default = $pages->GetDefaultPage();

if(isset($_POST['Login'])) {
	$username = trim(rtrim($_POST['username']));
	$password = $_POST['password'];

	$loginsuccess = $authenticate->Login($username,$password);

	if( $loginsuccess ){
		header('location:'.$pagelist[$default]['file']);
		exit();
	} else {
		header('location: login.php');
		exit();
	}
}

header('location:'.$pagelist[$default]['file']);