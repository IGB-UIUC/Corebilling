<?php
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}
?>
<h3>Devices In Use</h3>
<table class="table table-striped table-bordered">
	<tr class="title">
		<th>Device Name</th>
		<th>User Name</th>	
		<th>Location</th>
		<th>Status</th>
	</tr>
	
	<?php
	$device = new Device($sqlDataBase);
	$devicesInUse = $device->GetDevicesInUse();
	
	foreach($devicesInUse as $id=>$deviceUseInfo) {
		if($deviceUseInfo['lastseen']==null) {
			$lastSeen = "<b><font>---</font></b>";
		} elseif($deviceUseInfo['lastseen']>=4*60) {
			$lastSeen = "<span class=\"label label-danger\" style='font-size:18px; padding:.2em .6em'><span class=\"glyphicon-chevron-down glyphicon\"></span></span>";
		} else {
			$lastSeen = "<span class=\"label label-success\" style='font-size:18px; padding:.2em .6em'><span class=\"glyphicon-chevron-up glyphicon\"></span></span>";
		}
	
		if($deviceUseInfo['loggeduser']==0) {
			$loggedUser = "";
		} elseif($deviceUseInfo['loggeduser']==-1) {
			$loggedUser= "Unauthorized User (".$deviceUseInfo['unauthorized'].")";
		} else {
			$loggedUser = $deviceUseInfo['first']." ".$deviceUseInfo['last']." (".$deviceUseInfo['user_name'].")";
		}
		echo "<tr><td align=\"center\">".$deviceUseInfo['full_device_name']."</td><td align=\"center\">".$loggedUser."</td><td align=\"center\">".$deviceUseInfo['location']."</td><td align=\"center\">".$lastSeen."</td></tr>";
	}
	?>
</table>
<?php
	require_once 'includes/footer.inc.php';