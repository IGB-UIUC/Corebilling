<?php
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}
//Declare objects
$device = new Device($db);
$devicesList = $device->GetDevicesList();
$user = new User($db);
$userList = $user->GetAllUsers();
$group = new Group($db);
$groupList = $group->GetGroupsList();
$bills = new Bills($db);
$session = new Session($db);
$userCfop = new UserCfop($db);

$sessionIdSelected = 0;
$rowSelected = 0;

//TODO check permissions here

if (isset($_POST['startMonthSelected'])) {
	list($startmonth, $startyear) = explode(" ", $_POST['startMonthSelected']);
}
if (isset($_POST['endMonthSelected'])) {
	list($endmonth, $endyear) = explode(" ", $_POST['endMonthSelected']);
}

?>
<h4>Active Users</h4>

<form name="verifyForm" method="post" action="active_users.php" class="form-inline">
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
		$activeUsers = $user->GetActiveUsers($startyear, $startmonth, $endyear, $endmonth);
	?>
	<div class="panel panel-default">
		<div id="user_list_heading" class="panel-heading">
			<h3>Active Users:</h3>
		</div>
		<div class="panel-body">				
			<div class="row">
				<?php
				echo VisualizeData::ListSessionsTableHiddenCols($activeUsers,
					array('NetId', 'Name', 'Group', 'Department'),
					array('user_name', 'full_name', 'group_name', 'department_name'),
					array(),
					array('NetId', 'Name', 'Group', 'Department'), 'user_list', $rowSelected, true, false);
				?>
			</div>
		</div>
	</div>
</form>
<?php
require_once 'includes/footer.inc.php';