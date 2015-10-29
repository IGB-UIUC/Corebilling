<?php

if(isset($_POST['login']))
{
	$loginsuccess = $authenticate->Login($_POST['user_name'],$_POST['password']);
    if( !$loginsuccess ){
        header('Location: login.php');
    }
}


$authenticate->VerifySession();

if (!$authenticate->isVerified()){
	header('Location: login.php');
}
?>
