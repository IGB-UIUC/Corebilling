<?php
ob_start();
@session_start();
// setting up the web root and server root for
include('includes/config.php');
include('includes/auto_load_classes.php');
include('includes/mysql_connect.php');

//Sets up ldap connection
$authen = new LdapAuth ( LDAP_HOST, LDAP_PEOPLE_DN, LDAP_GROUP_DN,LDAP_PORT);
if(LDAPMAN_API_ENABLED){
	$ldapman = new LdapManager(LDAPMAN_API_URL, LDAPMAN_API_USERNAME, LDAPMAN_API_PASSWORD);
} else {
	$ldapman = new LdapManager(LDAPMAN_API_URL);
}

//Authenticates to website database
$authenticate = new Authenticate($sqlDataBase, $authen);

?>
