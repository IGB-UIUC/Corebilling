<?php
require_once 'includes/header.inc.php';
$access = $accessControl->GetPermissionLevel($authenticate->getAuthenticatedUser()->GetUserId(), AccessControl::RESOURCE_PAGE, $pages->GetPageId('Statistics'));
if($access == AccessControl::PERM_DISALLOW){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}

$department = new Group($sqlDataBase); // TODO this isn't confusing AT ALL
$device = new Device($sqlDataBase);
$user = new User ($sqlDataBase);
$session = new Session($sqlDataBase);
$firstSession = new Session($sqlDataBase);
$firstSession->LoadFirstSession();
$lastSession = new Session($sqlDataBase);
$lastSession->LoadLastSession();

$user->LoadUser($authenticate->getAuthenticatedUser()->getUserId());
$filtersArr = array();

//Apply filters to charts
if (isset($_POST['filtersSelection'])) {
	$startDate = $_POST['startDate'];
	$endDate = $_POST['endDate'];

	$interval = $_POST['interval'];
	if (isset($_POST['usersFilter'])) {
		$usersArr = $_POST['usersFilter'];
		$filtersArr['user'] = $usersArr;
	}

	if (isset($_POST['devicesFilter'])) {
		$devicesArr = $_POST['devicesFilter'];
		$filtersArr['device'] = $devicesArr;
	}

	if (isset($_POST['groupsFilter'])) {
		$groupsArr = $_POST['groupsFilter'];
		$filtersArr['group'] = $groupsArr;
	}
} else {
	$startDate = date("Y-m-d", strtotime("-1 month"));
	$endDate = date("Y-m-d");
	$interval = Session::DAY;
}
?>
<div class="alert alert-info">
	<h4>Statistics</h4>
	<p>View instrument usage statistics by user/device/group</p>
	<p>Hold the Shift key on keyboard to select multiple users/devices/groups in lists</p>
</div>

<form action="dev_statistics.php" method="POST">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3>Filters: </h3>
		</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-sm-4">
					<div class="form-group">
						<label class="label-control">Users</label>
						<select multiple size="10" id="user" name="usersFilter[]" class="form-control">
							<?php
							$userList = $user->GetAllUsers();
							foreach ($userList as $id => $userInfo) {
								echo "<option value=" . $userInfo['id'] . " " . ((@in_array($userInfo['id'], $filtersArr['user']) ? "selected" : "")) . ">" . $userInfo['user_name'] . "</option>";
							}
							?>
						</select>
					</div>
					<div class="form-group">
						<label class="label-control">
							Start Date
						</label>
						<input type="date" id="startDate" name="startDate" value="<?php echo $startDate; ?>" class="form-control">
					</div>
					<div class="form-group">
						<input class="btn btn-primary" type="submit" value="Select Filters" name="filtersSelection">
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group">
						<label class="label-control">Devices</label>
						<select multiple size="10" id="device" name="devicesFilter[]" class="form-control">
							<?php
							$deviceList = $device->GetDevicesList();
							foreach ($deviceList as $id => $deviceInfo) {
								echo "<option value=" . $deviceInfo['id'] . " " . ((@in_array($deviceInfo['id'], $filtersArr['device']) ? "selected" : "")) . ">" . $deviceInfo['device_name'] . "</option>";
							}
							?>
						</select>
					</div>
					<div class="form-group">
						<label class="label-control">
							End Date
						</label>
						<input type="date" id="endDate" name="endDate" value="<?php echo $endDate; ?>" class="form-control">
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group">
						<label class="label-control">Groups</label>
						<select multiple size="10" id="group" name="groupsFilter[]" class="form-control">
							<?php
							$groupList = $department->GetGroupsList();
							foreach ($groupList as $id => $groupInfo) {
								echo "<option value=" . $groupInfo['id'] . " " . ((@in_array($groupInfo['id'], $filtersArr['group']) ? "selected" : "")) . ">" . $groupInfo['group_name'] . "</option>";
							}
							?>
						</select>
					</div>
					<div class="form-group">
						<label class="label-control col-sm-4">Intervals</label>
						<select name="interval" class="form-control">
							<?php
							echo "<option value=" . Session::DAY . " " . ((Session::DAY == $interval) ? "selected" : "") . ">Daily</option>
								  <option value=" . Session::WEEK . " " . ((Session::WEEK == $interval) ? "selected" : "") . ">Weekly</option>
								  <option value=" . Session::YEAR . " " . ((Session::YEAR == $interval) ? "selected" : "") . ">Yearly</option>
								  <option value=" . Session::ALL_SESSIONS . " " . ((Session::ALL_SESSIONS == $interval) ? "selected" : "") . ">All Sessions</option>";
							?>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>

<div class="panel panel-default">
	<?php
	if ($interval > 0) {
	?>
	<div class="panel-heading"><h3>Graphs: </h3></div>


<?php
//Graph Device Time Line
		$sessionArr = $session->GetSessionsUsage($startDate, $endDate, $interval, $filtersArr);
		echo VisualizeData::GraphTimeLine($sessionArr, "start", "elapsed", "device_name", "Device Usage Time Line", "Usage (hours)");

		$sessionArr = $session->GetSessionsUsage($startDate, $endDate, $interval, $filtersArr);
		echo VisualizeData::GraphTimeLine($sessionArr, "start", "bill", "device_name", "Device Billed Time Line", "Usage ($)");

//Graph Users usage
		$sessionArr = $session->GetSessionsUsage($startDate, $endDate, Session::PERSON, $filtersArr);
		echo VisualizeData::GraphDataPie($sessionArr, "elapsed", "user_name", "User Usage");

//Graph Device usage
		$sessionArr = $session->GetSessionsUsage($startDate, $endDate, Session::DEVICE, $filtersArr);
		echo VisualizeData::GraphDataPie($sessionArr, "elapsed", "device_name", "Device Usage");

//Graph Group Usage
		$sessionArr = $session->GetSessionsUsage($startDate, $endDate, Session::GROUP, $filtersArr);
		echo VisualizeData::GraphDataPie($sessionArr, "elapsed", "group_name", "Group Usage");
		?>
	<?php
	} else {
		echo " <div class=\"panel-heading\"><h3>Sessions Table: </h3>
	</div>";
		$sessionArr = $session->GetSessionsUsage($startDate, $endDate, Session::ALL_SESSIONS, $filtersArr);
		//List session data in a table
		echo VisualizeData::ListSessionsTable($sessionArr, array('User Name', 'Device', 'Group', 'Start', 'Elapsed (Hrs)', 'Billed ($)'), array('user_name', 'device_name', 'group_name', 'start', 'elapsed', 'bill'), 'sessionData',0);
	}

	?>
</div>
<?php
require_once 'includes/footer.inc.php';
?>