<div class="alert alert-info">
<h4>Devices In Use</h4>
The following table displays which devices are currently being used and which users are using them.
</div>
<br>
<table class="table table-striped table-bordered">
<tr class="title">
<th>
	Device Name
</th>
<th>
	User Name
</th>	
<th>
	Location
</th>
<th>
	Status
</th>
</tr>

<?php
$device = new Device($sqlDataBase);
$devicesInUse = $device->GetDevicesInUse();

foreach($devicesInUse as $id=>$deviceUseInfo)
{
		if($deviceUseInfo['lastseen']==null)
		{
			$lastSeen = "<b><font>---</font></b>";
		}
		elseif($deviceUseInfo['lastseen']>=4*60)
		{
			$lastSeen = "<a class=\"btn btn-danger\" href=\"#\"><i class=\"icon-chevron-down icon-white\"></i></a>";
		}
		else
		{
			$lastSeen = "<a class=\"btn btn-success\" href=\"#\"><i class=\"icon-chevron-up icon-white\"></i></a>";
		}

		if($deviceUseInfo['loggeduser']==0)
		{
			$loggedUser = "";
		}
		elseif($deviceUseInfo['loggeduser']==-1)
		{
			$loggedUser= "Unauthorized User (".$deviceUseInfo['unauthorized'].")";
		}
		else
		{
			$loggedUser = $deviceUseInfo['first']." ".$deviceUseInfo['last']." (".$deviceUseInfo['user_name'].")";
		}
		echo "<tr><td align=\"center\">".$deviceUseInfo['full_device_name']."</td><td align=\"center\">".$loggedUser."</td><td align=\"center\">".$deviceUseInfo['location']."</td><td align=\"center\">".$lastSeen."</td></tr>";
}
?>
</table>