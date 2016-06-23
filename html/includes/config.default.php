<?php

//LDAP Settings
@define ('LDAP_HOST','');
@define ('LDAP_PEOPLE_DN', '');
@define ('LDAP_GROUP_DN', ''); // Leave blank for no group check
@define ('LDAP_PORT','389');

//MySQL settings
@define ('DB_USER','');
@define ('DB_PASSWORD','');
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
@define ('DEFAULT_USER_EMAIL_DOMAIN','');

//Admin Default
@define ('ADMIN_EMAIL','');
$ADMIN_EMAIL = array();

//Session Tracker users to ignore
$USER_EXCEPTIONS_ARRAY = array();


?>