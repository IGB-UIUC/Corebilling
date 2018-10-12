<?php
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}
//Declare objects
$bills = new Bills($db);
$session = new Session($db);
$userCfop = new UserCfop($db);
$stats = new Statistics($db);

$sessionIdSelected = 0;

//TODO check permissions here

if (isset($_POST['startMonthSelected'])) {
	list($startmonth, $startyear) = explode(" ", $_POST['startMonthSelected']);
}
if (isset($_POST['endMonthSelected'])) {
	list($endmonth, $endyear) = explode(" ", $_POST['endMonthSelected']);
}

?>
<h4>Reservation Usage</h4>

<form name="verifyForm" method="post" action="reservation_use.php" class="form-inline">
	<div class="well">
		<div class="form-group">
			<label>Start: </label>
			<select name="startMonthSelected" class="form-control">
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
			<select name="endMonthSelected" class="form-control">
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
			<input class="btn btn-primary btn-sm" type="submit" name="selectMonth" value="Load Billing Period">
		</div>
	</div>
	<?php
		$resUse = $stats->getReservationUsage($startyear,$startmonth,$endyear,$endmonth);
	?>
	<div class="panel panel-default">
		<div id="user_list_heading" class="panel-heading">
			<h3>Active Users:</h3>
		</div>
		<div class="panel-body">				
			<div class="row">
				<?php
				echo VisualizeData::ListSessionsTableHiddenCols($resUse,
					array('NetId', 'Reserved Time (Hours)', 'Used Time (Hours)', 'Used Time/Reserved Time', 'Missed Reservations', 'Deleted Reservations'),
					array('user_name', 'res_time', 'used_time', 'used_ratio', 'missed_res', 'deleted_res'),
					array(),
					array('NetId', 'Reserved Time (Hours)', 'Used Time (Hours)', 'Used Time/Reserved Time', 'Missed Reservations', 'Deleted Reservations'), 'user_list', false, false);
				?>
			</div>
		</div>
	</div>
</form>
<?php
require_once 'includes/footer.inc.php';