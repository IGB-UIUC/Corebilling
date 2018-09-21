<?php
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}
//Declare objects
$rateTypesList = array(Bills::CONTINUOUS_RATE => "Continuous", Bills::MONTHLY_RATE => "Monthly");
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
if (isset($_POST['createSession'])) {
	$startTimeStamp = strtotime($_POST['startDate'] . " " . $_POST['starttime']);
	$start = Date("Y-m-d H:i:s", $startTimeStamp);
	$stop = Date("Y-m-d H:i:s", $startTimeStamp + ($_POST['usage'] * 60 * 60));
	$session->CreateSession($_POST['user_id'], $start, $_POST['stop'], $_POST['status'], $_POST['device_id'], $_POST['description'], $_POST['cfop']);
	$session->SetRate($_POST['rate'] / 60);
	$session->UpdateSession();
	$session->ManualVerify();
}

if (isset($_POST['update_session'])) {
	$session->LoadSession($_POST['edit_session_id']);
	if ($session->GetUserID() == $_POST['user_id']) {
		$session->SetCfopId($_POST['user_cfop_id']);
	} else {
		$session->SetCfopId($userCfop->LoadDefaultCfopl($_POST['user_id']));
	}
	$session->SetUserID($_POST['user_id']);
	$session->SetDeviceID($_POST['device_id']);
	$session->SetRate($_POST['rate'] / 60);
	$session->SetElapsed( (strtotime($_POST['endtime'])-strtotime($_POST['starttime']))/60 );
	$session->SetStart(date('Y-m-d H:i:s', strtotime($_POST['starttime'])));
	$session->SetStop(date('Y-m-d H:i:s', strtotime($_POST['endtime'])));
	$session->UpdateSession();
	$sessionIdSelected = $_POST['edit_session_id'];
}

if (isset($_POST['applyAction'])) {

	foreach($rateTypesList as $id=>$rate)
	{
		if(isset($_POST['sessionsCheckbox'])) {

			switch ($_POST["selectAction"]) {
				case "defaultCfop":
					$bills->SetToDefaultCFOP($_POST['sessionsCheckbox']);
					break;
			}
		}
	}
}

if (isset($_GET['session_id'])) {
	if (isset($_GET['rowid'])) {
		$rowSelected = $_GET['rowid'];
	}
	if (isset($_POST['edit_session_row'])) {
		$rowSelected = $_POST['edit_session_row'];
	}
	$sessionIdSelected = $_GET['session_id'];
}

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

if ($sessionIdSelected > 0) {
	$session->LoadSession($sessionIdSelected);
	if(!isset($startmonth)){
		$startDate = $session->GetStart();
		$startDateArr = getdate(strtotime($startDate));
		$startmonth = $startDateArr['mon'];
		$endmonth = $startmonth;
		$startyear = $startDateArr['year'];
		$endyear = $startyear;
	}

	echo "<script>
	$(document).ready(function(){
		Element.prototype.documentOffsetTop = function () {
			return this.offsetTop + ( this.offsetParent ? this.offsetParent.documentOffsetTop() : 0 );
		};
		var top = document.getElementById( '" . $sessionIdSelected . "' ).documentOffsetTop() - ( window.innerHeight / 2 );
		window.scrollTo(0,top);

	});
	</script>";
}

?>
<h4>Facility Billing</h4>

<form name="verifyForm" method="post" action="facility_billing.php" class="form-inline">
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
		
		// TODO check supervisor perms here
		$monthlyUsage = $bills->GetMonthsCharges($startyear, $startmonth, $endyear, $endmonth, $rateTypeSelected);
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

			//Show Edit under Options
			$monthlyUsage[$rowId]['options'] = "<a id=" . $monthSession['id'] . " href=\"facility_billing.php?session_id=" . $monthSession['id'] . "&rowid=" . $rowId . "\">Edit</a>";

			//Total String
			$monthlyUsage[$rowId]['total'] = "$" . number_format($bills->CalcTotal($monthSession['elapsed'], $rateTypeSelected, $rate, $monthSession['min_use_time']), 2);

			//Full name
			//Cfop string
			$cfopString = UserCfop::formatCfop($monthSession['cfop']);
			$monthlyUsage[$rowId]['cfop'] = $cfopString;

			//If we want to edit this session info then load selected row with input input fields
			if ($session->GetSessionId() == $monthSession['id']) {
				//User options for edit session
				$userNameString = "<select name=\"user_id\" class=\"form-control input-xs\">";
				foreach ($userList as $id => $userToSelect) {
					$userNameString .= "<option value=" . $userToSelect["id"];
					if ($userToSelect["id"] == $monthSession['user_id']) {
						$userNameString .= " SELECTED";
					}
					$userNameString .= ">" . $userToSelect['user_name'] . "</option>";
				}
				$userNameString .= "</select>";
				$monthlyUsage[$rowId]['user_name'] = $userNameString;

				//Start Time Edit String
				$startTimeString = "<input type=\"datetime-local\" name=\"starttime\" value=\"" . date('Y-m-d\TH:i:s', strtotime($monthSession['start'])) . "\" class=\"form-control input-xs\">";
				$monthlyUsage[$rowId]['start'] = $startTimeString;
				
				//End Time Edit String
				$endTimeString = "<input type=\"datetime-local\" name=\"endtime\" value=\"" . date('Y-m-d\TH:i:s', strtotime($monthSession['stop'])) . "\" class=\"form-control input-xs\">";
				$monthlyUsage[$rowId]['stop'] = $endTimeString;

				//CFOP options for edit session
				$userCfopList = $userCfop->ListCfops($monthSession['user_id']);
				$cfopString = "<select name=\"user_cfop_id\" class=\"form-control input-xs\">";
				foreach ($userCfopList as $userCfopInfo) {
					$cfopString .= "<option value=" . $userCfopInfo['id'];
					if ($monthSession['cfop_id'] == $userCfopInfo['id']) {
						$cfopString .= " SELECTED";
					}
					$cfopString .= ">" . UserCfop::formatCfop($userCfopInfo['cfop']) . "</option>";
				}
				$cfopString .= "</select>";
				$monthlyUsage[$rowId]['cfop'] = $cfopString;

				//Device selection edit string
				$deviceString = "<select name=\"device_id\" class=\"form-control input-xs\">";
				foreach ($devicesList as $id => $deviceToSelect) {
					$deviceString .= "<option value=" . $deviceToSelect["id"];
					if ($deviceToSelect["id"] == $monthSession['device_id']) {
						$deviceString .= " SELECTED";
					}
					$deviceString .= ">" . $deviceToSelect["full_device_name"] . "</option>";
				}
				$deviceString .= "</select>";
				$monthlyUsage[$rowId]['full_device_name'] = $deviceString;

				//Elapsed time edit string
/*
				$elapsedTimeString = "<input type=\"text\" name=\"elapsed\" value=\"" . round(($monthSession['elapsed'] / 60), 2) . "\" class=\"form-control input-xs\">";
				$monthlyUsage[$rowId]['elapsed'] = $elapsedTimeString;
*/

				//Min use time edit string
/*
				$minUseTimeString = "<input type=\"text\" name=\"min_use_time\" value=\"" . round(($monthSession['min_use_time'] / 60), 2) . "\" class=\"form-control input-xs\">";
				$monthlyUsage[$rowId]['min_use_time'] = $minUseTimeString;
*/

				//Rate String
				$rateString = "<input type=\"text\" name=\"rate\" value=\"" . round(($rate * 60), 2) . "\" class=\"form-control input-xs\">";
				$monthlyUsage[$rowId]['rate'] = $rateString;

				//Description Edit String
				$descriptionString = "<textarea name=\"description\" class=\"form-control input-xs\">" . $monthSession['description'] . "</textarea>";
				$monthlyUsage[$rowId]['description'] = $descriptionString;

				//Options
				$optionsString = "<a id=" . $monthSession['id'] . " name=".$monthSession['id']."></a>
										<input type=\"submit\" value=\"Update\" name=\"update_session\" class=\"btn btn-primary btn-xs\">
										<input type=\"hidden\" name=\"edit_session_id\" value=" . $monthSession['id'] . ">
										<input type=\"hidden\" name=\"edit_session_row\" value=" . $rowId . ">";
				$monthlyUsage[$rowId]['options'] = $optionsString;
			}

		}
		?>
			<div class="row">
				<?php
				echo VisualizeData::ListSessionsTableHiddenCols($monthlyUsage,
					array('id', 'NetId', 'Name', 'Start','End', 'Date', 'CFOP', 'Inst.', 'Hrs', 'Elapsed', 'Min. Hrs', '$/h', 'Rate', 'Total', 'Group', 'Department', 'Opt.'),
					array('id', 'user_name', 'full_name', 'start','stop', 'Date', 'cfop', 'full_device_name', 'elapsed', 'elapsed_unrounded', 'min_use_time', 'rate', 'rate_name', 'total', 'group_name', 'department_name', 'options'),
					array('Date','Elapsed','elapsed_unrounded'),
					array('NetId', 'Name', 'Date', 'CFOP', 'Inst.', 'Elapsed', 'Min. Hrs', '$/h', 'Rate', 'Total', 'Group', 'Department'), "Rate".$rateTypeSelected, $rowSelected, true, false);
				?>
			</div>


			<input class="btn btn-primary btn-sm" type="submit" name="applyAction" value="Apply Action">
			<select name="selectAction" class="form-control">
				<option value="none">none</option>
				<option value="defaultCfop">Use Default CFOP</option>
			</select> on checked items.
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