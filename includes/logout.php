<?php

if(isset($_POST['login']))
{
    if(!$authenticate->Login($_POST['user_name'],$_POST['password']));
    {
        echo $authenticate->getLogonError();
    }

}

if(isset($_POST['logout']))
{
    $authenticate->Logout();
}

if($authenticate->isVerified()) {
	echo "<form class=\"navbar-form form-inline pull-right\" action=\"./index.php\" method=POST>";
	echo "<input name=\"logout\" type=\"submit\" class=\"btn btn-danger\" id=\"Logout\" value=\"Logout: ".$authenticate->getAuthenticatedUser()->GetFirst()." ".$authenticate->getAuthenticatedUser()->GetLast()."\" >";
	echo "</form>";
}

?>
