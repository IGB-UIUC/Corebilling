<?php
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}
$device = new Device($db);
$res = new Reservation($db);

if (isset ($_GET['device'])) {
	$device->LoadDevice($_GET['device']);
} else {
	$device->LoadDevice(43); // Load the NMR by default TODO find a better way to grab the default
}
$date = new DateTime();
if(isset($_GET['date'])){
	$date = DateTime::createFromFormat("Y-m-d",$_GET['date']);
}
$reservations = $res->EventsRange($date->format("Y-m-d")." 00:00:00", $date->format("Y-m-d")." 23:59:59", null, $device->GetDeviceId(), false);
$sessions = Session::GetSessions($db, $date->format("Y-m-d"), $device->GetDeviceId());
?>

<h3>Usage Comparison<?php if($device->GetDeviceId()!=0){echo " - ".$device->GetFullName();} ?> - <?php echo $date->format("m/d/Y"); ?></h3>
<div class="well">
	<form method="GET" name="calform" class="form-inline">
		<div class="form-group">
			<select name="device" class="form-control" onChange='document.calform.submit();'>
				<?php
				$deviceList = $device->GetDevicesList();
				foreach ($deviceList as $id => $availDevices) {
					if (($availDevices['status_id']==1 || $availDevices['status_id']==3)) {
						echo "<option value=" . $availDevices ['id'];
						if ($availDevices['id'] == $device->GetDeviceId()) {
							echo " SELECTED";
						}
						echo ">" . $availDevices ['full_device_name'] . "</option>";
					}
				}
				?>
			</select>
		</div>
		<div class="form-group">
			<input type="text" id="date" name="date" class="form-control" onChange='document.calform.submit();' value="<?php echo $date->format("Y-m-d"); ?>" />
		</div>
	</form>
</div>
<div class="row">
	<div class="col-xs-3 col-sm-2 col-lg-1">
		<div style="height: 100px"><h4>Reservations</h4></div>
		<div style="height: 100px"><h4>Usage</h4></div>
	</div>
	<div class="col-xs-9 col-sm-10 col-lg-11">
		<div class="comprow" id="resrow">
			<?php
			for($i = 0; $i<count($reservations); $i++){
				$start = strtotime($reservations[$i]['starttime']);
				$stop = strtotime($reservations[$i]['stoptime']);
				$startofday = strtotime($date->format("Y-m-d")." 00:00:00");
				$width = ($stop - $start) / (24*60*60) * 100;
				$left = ($start - $startofday) / (24*60*60) * 100;
				$startstr = DateTime::createFromFormat("Y-m-d H:i:s",$reservations[$i]['starttime'])->format("g:i a");
				$stopstr = DateTime::createFromFormat("Y-m-d H:i:s",$reservations[$i]['stoptime'])->format("g:i a");
				$username = $reservations[$i]['user_name'];
				$group = $reservations[$i]['group_name'];
				
				echo "<div class='compcell' style='width: $width%; left: $left%' data-container='body' data-toggle='popover' data-placement='top' title='$username' data-content='$startstr - $stopstr<br/>Group: $group'>$username<br/>$startstr - $stopstr<br/>Group: $group</div>";
			}
			?>
		</div>
		<div class="comprow" id="sessrow">
			<?php
			for($i = 0; $i<count($sessions); $i++){
				$start = strtotime($sessions[$i]['start']);
				$stop = strtotime($sessions[$i]['stop']);
				$startofday = strtotime($date->format("Y-m-d")." 00:00:00");
				$endofday = strtotime($date->format("Y-m-d")." 23:59:59");
				$right = ($endofday - $stop) / (24*60*60) * 100;
				$left = ($start - $startofday) / (24*60*60) * 100;
				$style = "";
				if($right < 0){
					$right = 0;
					$style .= " border-right: none; border-top-right-radius: 0; border-bottom-right-radius: 0;";
				}
				if($left < 0){
					$left = 0;
					$style .= " border-left: none; border-top-left-radius: 0; border-bottom-left-radius: 0;";
				}
				$startstr = DateTime::createFromFormat("Y-m-d H:i:s",$sessions[$i]['start'])->format("g:i a");
				$stopstr = DateTime::createFromFormat("Y-m-d H:i:s",$sessions[$i]['stop'])->format("g:i a");
				$username = $sessions[$i]['user_name'];
				$group = $sessions[$i]['group_name'];
				echo "<div class='compcell' style='right: $right%; left: $left%;$style' data-container='body' data-toggle='popover' data-placement='bottom' title='$username' data-content='$startstr - $stopstr<br/>Group: $group'>$username<br/>$startstr - $stopstr<br/>Group: $group</div>";
			}
			?>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(function(){
		$('[data-toggle="popover"]').popover({html:true});
		$('select').select2({'width':'element'});
		$('#date').datepicker({
			dateFormat: "yy-mm-dd",
		}).datepicker("setDate",$('#date').val());
	});
</script>
<?php
	require_once 'includes/footer.inc.php';
	?>