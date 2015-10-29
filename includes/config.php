<?php

//LDAP Settings
// @define ('LDAP_HOST','ldaps://ad.uillinois.edu');
@define ('LDAP_HOST','ldap://auth.igb.illinois.edu');
// @define ('LDAP_PEOPLE_DN', 'OU=People,DC=ad,DC=uillinois,DC=edu');
@define ('LDAP_PEOPLE_DN', 'ou=People,dc=igb,dc=uiuc,dc=edu');
// @define ('LDAP_GROUP_DN', 'CN=BIOTECH FLOWCYT USERS,OU=Flowcyt,OU=Biotech,OU=Urbana,DC=ad,DC=uillinois,DC=edu');
@define ('LDAP_GROUP_DN', 'cn=cnrg,ou=group,dc=igb,dc=uiuc,dc=edu');
@define ('LDAP_PORT','389');

//MySQL settings
@define ('DB_USER','flowcyt_user');
@define ('DB_PASSWORD','igb123');
@define ('DB_HOST','localhost');
@define ('DB_NAME','coreapp_flowcyt');

//Page Settings
@define ('PAGE_TITLE', 'Instrument Tracking');
@define ('DEFAULT_PAGE',"Latest News");


//User Defaults
@define ('DEFAULT_USER_ROLE_ID',3); //No Role
@define ('DEFAULT_USER_RATE_ID',9);
@define ('DEFAULT_USER_STATUS_ID',5); //Disabled does not allow user to log in
@define ('DEFAULT_USER_GROUP_ID',0); //No Group
@define ('DEFAULT_USER_DEPARTMENT_ID',0); //No department
@define ('DEFAULT_USER_EMAIL_DOMAIN','illinois.edu');

//Admin Default
@define ('ADMIN_EMAIL','jleigh@illinois.edu');
$ADMIN_EMAIL = array('jleigh@illinois.edu');

//Session Tracker users to ignore
$USER_EXCEPTIONS_ARRAY = array('system','administrator','cnrg','local service','BDFSE','Accuri','Admin');


?>
