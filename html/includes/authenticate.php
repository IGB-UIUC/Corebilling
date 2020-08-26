<?php
$authenticate->VerifySession();

if (!$authenticate->isVerified()){
	header('Location: login.php');
}

$login_user = $authenticate->getAuthenticatedUser();

