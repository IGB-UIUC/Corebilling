<?php
ob_start();
@session_start();
// setting up the web root and server root for
include('includes/config.php');
include('includes/auto_load_classes.php');
include('includes/mysql_connect.php');
include('includes/authenticate.php');

$pages = new Pages($sqlDataBase);
$pages->SetDefaultPage(DEFAULT_PAGE);

?>
