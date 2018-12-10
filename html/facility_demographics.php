<?php
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}
//Declare objects
$rateTypesList = array(Bills::CONTINUOUS_RATE => "Continuous", Bills::MONTHLY_RATE => "Monthly");
$devicesList = Device::getAllDevices($db);
$userList = User::getAllUsers($db);
$groupList = Group::getAllGroups($db);
$bills = new Bills($db);
$userCfop = new UserCfop($db);

if (isset($_POST['startMonthSelected'])) {
	list($startmonth, $startyear) = explode(" ", $_POST['startMonthSelected']);
}
if (isset($_POST['endMonthSelected'])) {
	list($endmonth, $endyear) = explode(" ", $_POST['endMonthSelected']);
}


if(isset($_POST['rateTypeSelected']))
{
	$rateTypeSelected = $_POST['rateTypeSelected'];
}
else
{
	$rateTypeSelected = Bills::CONTINUOUS_RATE;
}

?>
	<h4>Facility Demographics</h4>

	<form name="verifyForm" method="post" class="form-inline">
		<div class="well">
			<div class="form-group">
				<label>Start: </label>
				<select id="startSelect" name="startMonthSelected" class="form-control">
					<?php
					$availableMonths = $bills->GetAvailableBillingMonths();

					foreach ($availableMonths as $id => $availMonth) {
						if(!isset($startmonth)){
							$startmonth = $availMonth['month'];
							$endmonth = $startmonth;
							$startyear = $availMonth['year'];
							$endyear = $startyear;
						}
						echo "<option value=\"" . $availMonth['month'] . " " . $availMonth['year'] . "\"";
						if ($availMonth['month'] == $startmonth && $availMonth['year'] == $startyear) {
							echo " SELECTED";
						}
						echo ">" . $availMonth['mon_yr'] . "</option>";
					}

					?>
				</select>
			</div>
			<div class="form-group">
				<label>End: </label>
				<select id="endSelect" name="endMonthSelected" class="form-control">
					<?php
					$availableMonths = $bills->GetAvailableBillingMonths();

					foreach ($availableMonths as $id => $availMonth) {
						echo "<option value=\"" . $availMonth['month'] . " " . $availMonth['year'] . "\"";
						if ($availMonth['month'] == $endmonth && $availMonth['year'] == $endyear) {
							echo " SELECTED";
						}
						echo ">" . $availMonth['mon_yr'] . "</option>";
					}

					?>
				</select>
			</div>
			<div class="form-group">
				<label>Rate type: </label>
				<select name="rateTypeSelected" class="form-control">
					<?php

					foreach ($rateTypesList as $rateTypeId => $rateTypeName) {
						echo "<option value=\"" . $rateTypeId . "\" ";
						if ($rateTypeSelected == $rateTypeId) {
							echo " SELECTED";
						}
						echo ">" . $rateTypeName . "</option>";
					}

					?>
				</select>
			</div>
			<div class="form-group">
				<input class="btn btn-primary btn-sm" type="submit" name="selectMonth" value="Load Billing Period">
			</div>
			<br/><br/>
			<strong>Filter: </strong>
			<div class="form-group">
				<label style="font-weight:normal">NetId:</label>
				<input class="form-control input-sm" type="search" id="netidfilter" name="netidfilter" oninput="searchCol(<?php echo "Rate".$rateTypeSelected; ?>,2,'netidfilter')">
			</div>
			<div class="form-group">
				<label style="font-weight:normal">Name:</label>
				<input class="form-control input-sm" type="search" id="namefilter" name="namefilter" oninput="searchCol(<?php echo "Rate".$rateTypeSelected; ?>,3,'namefilter')">
			</div>
			<div class="form-group">
				<label style="font-weight:normal">Device:</label>
				<select id="devicefilter" class="form-control" onchange="matchCol(<?php echo "Rate".$rateTypeSelected; ?>,8,'devicefilter')">
					<option value="">All</option>
					<?php
					foreach($devicesList as $device){
						if($device['status_id']==1 || $device['status_id']==2 || $device['status_id']==4){
							echo "<option>".$device['full_device_name']."</option>";
						}
					}
					?>
				</select>
			</div>
			<div class="form-group">
				<label style="font-weight:normal">Group:</label>
				<select id="groupfilter" class="form-control" onchange="matchCol(<?php echo "Rate".$rateTypeSelected; ?>,14,'groupfilter')">
					<option value="">All</option>
					<?php
					foreach($groupList as $group){
						echo "<option>".$group['group_name']."</option>";
					}
					?>
				</select>
			</div>
		</div>
		<?php
		$bills->setGroupBy(0);

		if ($rateTypeSelected== Bills::MONTHLY_RATE) {
			$bills->setGroupBy(Bills::GROUP_DEVICE_USER);
		}

		$monthlyUsage = $bills->GetMonthsCharges($startyear, $startmonth, $endyear, $endmonth, $rateTypeSelected, true);
		?>
		<div class="panel panel-default">
			<div id="<?php echo "Rate".$rateTypeSelected."_heading"; ?>" class="panel-heading">
				<h3>Billed <?php echo $rateTypesList[$rateTypeSelected];?>:</h3>
			</div>
			<div class="panel-body">
				<?php
				//Go through each month session
				foreach ($monthlyUsage as $rowId => $monthSession) {
					$rate = $monthSession['rate'];

					//date string
					$monthlyUsage[$rowId]['Date'] = date('m/d/y', strtotime($monthlyUsage[$rowId]['start']));

					//change rate to hours
					$monthlyUsage[$rowId]['rate'] = round($monthSession['rate'] * 60, 2);

					//Change usage to hours
					$monthlyUsage[$rowId]['elapsed'] = round($monthSession['elapsed'] / 60, 2);
					$monthlyUsage[$rowId]['elapsed_unrounded'] = $monthSession['elapsed'] / 60;

					//Minimum usage time
					$monthlyUsage[$rowId]['min_use_time'] = round(($monthSession['min_use_time'] / 60), 2);

					//Total String
					$monthlyUsage[$rowId]['total'] = "$" . number_format($bills->CalcTotal($monthSession['elapsed'], $rateTypeSelected, $rate, $monthSession['min_use_time']), 2);

					//Full name
					//Cfop string
					$cfopString = UserCfop::formatCfop($monthSession['cfop']);
					$monthlyUsage[$rowId]['cfop'] = $cfopString;

				}
				?>
				<div class="row">
					<?php
					echo VisualizeData::ListSessionsTableHiddenCols($monthlyUsage,
						array('id', 'NetId', 'Name', 'Start','End', 'Date', 'CFOP', 'Inst.', 'Hrs', 'Elapsed', 'Min. Hrs', '$/h', 'Rate', 'Total', 'Group', 'Department', 'Edu. Level', 'Gender', 'Underrep.'),
						array('id', 'user_name', 'full_name', 'start','stop', 'Date', 'cfop', 'full_device_name', 'elapsed', 'elapsed_unrounded', 'min_use_time', 'rate', 'rate_name', 'total', 'group_name', 'department_name', 'edu_level', 'gender', 'underrepresented'),
						array('Date','Elapsed','elapsed_unrounded'),
						array('NetId', 'Name', 'Date', 'CFOP', 'Inst.', 'Elapsed', 'Min. Hrs', '$/h', 'Rate', 'Total', 'Group', 'Department', 'Edu. Level', 'Gender', 'Underrep.'), "Rate".$rateTypeSelected, true, false, 'start');
					?>
				</div>
			</div>
		</div>
	</form>
	<script type="text/javascript">

        $('#groupfilter').select2({'width':'element'});
        $('#devicefilter').select2({'width':'element'});
        $('#startSelect').select2({'width':'element'});
        $('#endSelect').select2({'width':'element'});

	</script>
<?php
require_once 'includes/footer.inc.php';