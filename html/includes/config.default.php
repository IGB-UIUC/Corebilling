<?php

//LDAP Settings
@define ('LDAP_HOST','');
@define ('LDAP_PEOPLE_DN', '');
@define ('LDAP_GROUP_DN', ''); // Leave blank for no group check
@define ('LDAP_PORT','389');

@define('LDAPMAN_API_ENABLED',false);
@define('LDAPMAN_API_URL','');
@define('LDAPMAN_API_USERNAME','');
@define('LDAPMAN_API_PASSWORD','');
@define('LDAPMAN_DEVICE_PREFIX', '');
@define('LDAPMAN_PI_PREFIX', '');

@define('CORESERVER_ENABLED', false);

//MySQL settings
@define ('DB_USER','');
@define ('DB_PASSWORD','');
@define ('DB_HOST','localhost');
@define ('DB_NAME','coreapp_flowcyt');

//Page Settings
@define ('PAGE_TITLE', 'Instrument Tracking');
@define ('DEFAULT_PAGE',"Latest News");

// Authentication timeout
@define ('LOGIN_TIMEOUT', 8*60*60);

//User Defaults
@define ('DEFAULT_USER_ROLE_ID',3); //No Role
@define ('DEFAULT_USER_RATE_ID',9);
@define ('DEFAULT_USER_STATUS_ID',5); //Disabled does not allow user to log in
@define ('DEFAULT_USER_GROUP_ID',0); //No Group
@define ('DEFAULT_USER_DEPARTMENT_ID',0); //No department
@define ('DEFAULT_USER_EMAIL_DOMAIN','');

@define ('CAL_DEFAULT_COLOR','#3a87ad');
@define ('CAL_TRAINING_COLOR','#6c3cae');
@define ('CAL_MISSED_COLOR','#ae3c3c');
@define ('CAL_ROOM_COLOR','#ad603a');

@define ('RESERVE_ROOM', false);

//Admin Default
@define ('ADMIN_EMAIL','');
$ADMIN_EMAIL = array();

//Session Tracker users to ignore
$USER_EXCEPTIONS_ARRAY = array();

@define('__ENABLE_LOG__',true);
@define('__LOG_FILE__','');
