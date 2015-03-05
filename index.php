<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include('includes/initializer.php');

//Verify the person is allowed to log into the application
$authenticate->VerifySession();


include('includes/header.php');

//display navigation if user is verified
if ($authenticate->isVerified()) {
    include('includes/navigation.php');
}

//Main page contents
include('includes/contents.php');

//Close SQL connection
include('includes/mysql_close.php');

//Show footer
include('includes/footer.php')
?>