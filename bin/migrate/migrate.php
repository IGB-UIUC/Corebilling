<?php
$sapi_type = php_sapi_name();
// If run from command line
if ($sapi_type != 'cli') {
	echo "Error: This script can only be run from the command line.\n";
	exit;
}
error_reporting(E_ALL);

//MySQL settings
@define ('DB_USER','flowcyt_user');
@define ('DB_PASSWORD','igb123');
@define ('DB_HOST','localhost');
@define ('DB_NAME','igb_instru');
$sqlDataBase = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER,DB_PASSWORD);

// Drop all tables
echo "Clearing database\n";
$tables = "show tables";
$tableArr = $sqlDataBase->query($tables)->fetchAll(PDO::FETCH_ASSOC);
$tablecol = "Tables_in_".DB_NAME;

foreach($tableArr as $table){
	$drop = "drop table ".$table[$tablecol];
	$sqlDataBase->query($drop);
}

// Dump v1 database into new database
echo "Importing v1 database\n";
$mysqlcommand = "mysql -u".DB_USER." -p".DB_PASSWORD." -h ".DB_HOST." -D ".DB_NAME." < old_db.sql 2> /dev/null";
$output = shell_exec($mysqlcommand);

// Rename all tables to old_<tablename>
$tables = "show tables";
$tableArr = $sqlDataBase->query($tables)->fetchAll(PDO::FETCH_ASSOC);
$tablecol = "Tables_in_".DB_NAME;

foreach($tableArr as $table){
	$rename = "alter table ".$table[$tablecol]." rename old_".$table[$tablecol];
	$sqlDataBase->query($rename);
}

// Create new tables
//  Access_control needs role/page data, pages needs all data, rate_types needs all data, user_roles needs all data
echo "Setting up v2 database structure\n";
$mysqlcommand = "mysql -u".DB_USER." -p".DB_PASSWORD." -h ".DB_HOST." -D ".DB_NAME." < new_db.sql 2> /dev/null";
$output = shell_exec($mysqlcommand);

// Migrate info
echo "Migrating data\n";
$articles = "insert into articles select ID as id, time as created, text, title, userid as user_id from old_articles";
$sqlDataBase->query($articles);
$departments = "insert into departments select id, departmentname as department_name, description, department_code from old_departments";
$sqlDataBase->query($departments);
$device = "insert into device select ID as id, devicename as device_name, location, description, fdname as full_device_name, statusid as status_id, loggeduser, lasttick, unauthorized, devicetoken as device_token from old_device";
$sqlDataBase->query($device);
$devicerate = "insert into device_rate select r.ID as id, r.rate as rate, r.deviceid as device_id, r.rateid as rate_id, 0 as min_use_time, d.ratetype as rate_type_id from old_devicerate r left join old_device d on r.deviceid=d.ID";
$sqlDataBase->query($devicerate);
$deviceperm = "insert into access_control (participant_id,resource_type_id,resource_id,permission,participant_type_id) select 3 as participant_id, 1 as resource_type_id, deviceid as resource_id, 1 as permission, 0 as participant_type_id from old_device_perm where permissionid=5 and permgroupid=4"; // Permissionid 5 means "allow"
$sqlDataBase->query($deviceperm);
$eventinfo = "insert into reservation_info select ID as id, deviceid as device_id, userid as user_id, start, stop, description, training, date_created from old_event_info";
$sqlDataBase->query($eventinfo);
$groups = "insert into `groups` select ID as id, groupname as group_name, description, departmentid as department_id from old_groups";
$sqlDataBase->query($groups);
$rates = "insert into rates select ID as id, ratename as rate_name from old_rates";
$sqlDataBase->query($rates);
$session = "insert into `session` select ID as id, userid as user_id, start, stop, status, deviceid as device_id, elapsed, rate, description, 0 as cfop_id, 0 as min_use_time, 1 as rate_type_id from old_session";
$sqlDataBase->query($session);
$status = "insert into status select ID as id, statusname, type from old_status";
$sqlDataBase->query($status);
$users = "insert into users select ID as id, username as user_name, email, first, last, groupid as group_id, grank, '' as rate, hidden, rateid as rate_id, departmentid as department_id, statusid as status_id, usertypeid as user_role_id, date_added, MD5(RAND()) as secure_key from old_users";
$sqlDataBase->query($users);

// Add a few permissions
// admins need admin rights to all devices
$adminRights = "replace into access_control (participant_id, resource_type_id, resource_id, permission, participant_type_id) select 1 as participant_id, 1 as resource_type_id, ID as resource_id, 2 as permission, 0 as participant_type_id from old_device";
$sqlDataBase->query($adminRights);
// TODO user perms not being pulled in correctly

// CFOPs require a bit of massaging
echo "Building cfop history\n";
// First, add a couple indexes to old_session. Cuts the next query down by about 100x
$index = "create index cfop on old_session (cfop)";
$sqlDataBase->query($index);
$index = "create index start on old_session (start)";
$sqlDataBase->query($index);
$index = "create index userid on old_session (userid)";
$sqlDataBase->query($index);

// Insert them as-is
$usercfop = "insert into user_cfop (user_id,cfop,description,active,default_cfop,created) select s.userid as user_id, s.cfop as cfop, '' as description, 1 as active, 0 as default_cfop, s.start as created from `old_session` s left join `old_session` s1 on s.userid=s1.userid and s.cfop=s1.cfop and s.start > s1.start where s1.id is null and s.cfop is not null and s.cfop != ''";
$sqlDataBase->query($usercfop);

// Update latest cfops to default
//  If user has cfop in old_users, use that, otherwise use the most recently used cfop from old_session
$oldusers = "select * from old_users";
$oldusersArr = $sqlDataBase->query($oldusers)->fetchAll(PDO::FETCH_ASSOC);

$checkcfopdefault = "select count(*) as count from user_cfop where user_id=:userid and cfop=:cfop";
$checkcfopdefaultstmt = $sqlDataBase->prepare($checkcfopdefault);
$insertcfopdefault = "insert into user_cfop (user_id,cfop,description,active,default_cfop,created) values (:userid,:cfop,'',1,1,NOW())";
$insertcfopdefaultstmt = $sqlDataBase->prepare($insertcfopdefault);
$updatecfopdefault = "update user_cfop set default_cfop=1 where user_id=:userid and cfop=:cfop";
$updatecfopdefaultstmt = $sqlDataBase->prepare($updatecfopdefault);
foreach($oldusersArr as $user){
	$args = array(":cfop"=>$user['cfopl'],":userid"=>$user['ID']);
	$checkcfopdefaultstmt->execute($args);
	$cfopexists = $checkcfopdefaultstmt->fetch(PDO::FETCH_ASSOC)['count']==1;
	if($cfopexists){
		$updatecfopdefaultstmt->execute($args);		
	} else {
		$insertcfopdefaultstmt->execute($args);
	}
}

$cfopselect = "select * from user_cfop";
$cfopArr = $sqlDataBase->query($cfopselect)->fetchAll(PDO::FETCH_ASSOC);

echo "Referencing cfops in sessions\n";
// Reference them in sessions
$sessionupdate = "update session set cfop_id=:cfopid where id in (select ID from old_session where cfop=:cfop)";
$sessionupdatestmt = $sqlDataBase->prepare($sessionupdate);
foreach($cfopArr as $cfop){
	$args = array(":cfopid"=>$cfop['id'],":cfop"=>$cfop['cfop']);
	$sessionupdatestmt->execute($args);
	
}
// TODO format CFOPS consistently
foreach($cfopArr as $cfop){
	if(strlen($cfop['cfop'])==19){
		// Add dashes
	}
}

// Delete old tables
echo "Removing v1 tables\n";
$tables = "show tables like 'old_%'";
$tableArr = $sqlDataBase->query($tables)->fetchAll(PDO::FETCH_ASSOC);
$tablecol = "Tables_in_".DB_NAME." (old_%)";

foreach($tableArr as $table){
	$drop = "drop table ".$table[$tablecol];
	$sqlDataBase->query($drop);
}