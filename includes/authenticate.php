<?php
//Sets up ldap connection
$authen = new LdapAuth ( LDAP_HOST, LDAP_PEOPLE_DN, LDAP_GROUP_DN,LDAP_PORT);

//Authenticates to website database
$authenticate = new Authenticate($sqlDataBase, $authen);

//Loads access control for website which controls device and web page access
$accessControl = new AccessControl($sqlDataBase);
?>
