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
$selectedGroup = new Group($db);
$allUsers = User::getAllActiveUsers($db);
$allDevices = Device::getAllDevices($db);
$allGroups = Group::getAllGroups($db);


// First, add ldap groups
foreach($allGroups as $group){
    if($group['netid'] !== null){
        $gid = LDAPMAN_PI_PREFIX.$group['netid'];
        if($ldapman->getGroup($gid) === null){
            echo "Adding ldap group '$gid'\n";
            $ldapman->addGroup($gid, "Core ".$group['netid']." PI group");
        }
    }
}

// Next, add users to groups
foreach($allUsers as $user){
    echo "Processing ".$user['user_name']."\n";
    $selectedUser->load($user['id']);
    foreach($allDevices as $device){
        $ldapGroup = LDAPMAN_DEVICE_PREFIX. $device['device_name'];
        if($selectedUser->hasAccessTo($device['id'])){
            if(!$ldapman->isMemberOf($user['user_name'], $ldapGroup)) {
                echo "\tAdding " . $user['user_name'] . " to " . $ldapGroup . "... ";
                if ( $ldapman->addGroupMember($ldapGroup, $user['user_name']) ) {
                    echo "\n";
                } else {
                    echo "failed.\n";
                }
            }
        } else {
            if($ldapman->isMemberOf($user['user_name'], $ldapGroup)){
                echo "\tRemoving ".$user['user_name']." from ".$ldapGroup."... ";
                if($ldapman->removeGroupMember($ldapGroup, $user['user_name'])){
                    echo "\n";
                } else {
                    echo "failed.\n";
                }
            }
        }
    }
    foreach($selectedUser->getGroupIds() as $groupId){
        $selectedGroup->load($groupId);
        if($selectedGroup->getNetid() != null) {
            $ldapGroup = LDAPMAN_PI_PREFIX . $selectedGroup->getNetid();
            if(!$ldapman->isMemberOf($user['user_name'], $ldapGroup)) {
                echo "\tAdding " . $user['user_name'] . " to " . $ldapGroup . "... ";
                if ( $ldapman->addGroupMember($ldapGroup, $user['user_name']) ) {
                    echo "\n";
                } else {
                    echo "failed.\n";
                }
            }
        }
        // TODO we need a way to remove users if they are no longer in the PI group. Not urgent
    }
}