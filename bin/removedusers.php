<?php
// setting up the web root and server root for
include('../html/includes/config.php');
include('../html/includes/auto_load_classes.php');
include('../html/includes/mysql_connect.php');

//Sets up ldap connection
if(LDAPMAN_API_ENABLED){
	$ldapman = new LdapManager(LDAPMAN_API_URL, LDAPMAN_API_USERNAME, LDAPMAN_API_PASSWORD);
} else {
	$ldapman = new LdapManager(LDAPMAN_API_URL);
}

$selectedUser = new User($db);
$allUsers = $selectedUser->GetUsers(5);

foreach($allUsers as $user){
	if($ldapman->getUser($user['user_name']) == null){
		echo $user['user_name']."\n";
	}
}