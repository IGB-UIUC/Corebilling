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
$allUsers = $selectedUser->GetAllUsers();
$dev = new Device($db);
$allDevices = $dev->GetDevicesList();
$accessControl = new AccessControl($db);

foreach($allDevices as $device){
	if($device['status_id']==1){ // Only devices with tracking enabled
		echo "Processing ".$device['device_name']."\n";
		foreach($allUsers as $user){
			$accessExists = $accessControl->AccessExists(AccessControl::RESOURCE_DEVICE, $device['id'], AccessControl::PARTICIPANT_USER, $user['id']);
			if($accessExists !== 0 && $accessExists['permission'] != 0){
				echo "\tAdding ".$user['user_name']." to ".LDAPMAN_GROUP_PREFIX.$device['device_name']."... ";
				if($ldapman->addGroupMember(LDAPMAN_GROUP_PREFIX.$device['device_name'],$user['user_name'])){
					echo "\n";
				} else {
					echo $user['user_name']." does not exist.\n";
				}
			}
		}
	}
}