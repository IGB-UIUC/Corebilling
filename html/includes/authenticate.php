<?php

$authenticate->VerifySession();

if (!$authenticate->isVerified()){
	header('Location: login.php');
}
?>
