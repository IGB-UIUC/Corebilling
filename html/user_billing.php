<?php
require_once 'includes/header.inc.php';

$rateTypesList = array(Bills::CONTINUOUS_RATE => "Continuous", Bills::MONTHLY_RATE => "Monthly");

$userToBill = new User($sqlDataBase);
$bills = new Bills($sqlDataBase);
$userCfop = new UserCfop($sqlDataBase);

if($login_user->isAdmin()){
	$selectableUsersList = $userToBill->GetAllUsers();
} elseif($login_user->isSupervisor()){
	$selectableUsersList = $userToBill->GetGroupUsers($authenticate->getAuthenticatedUser()->GetGroupId());
} else {
	$selectableUsersList = array();
}

if (isset($_POST['monthSelected'])) {
	list($month, $year) = explode(" ", $_POST['monthSelected']);
} else {
	$month = Date("n");
	$year = Date("Y");
}

if (isset($_POST['selectedUser'])) {
	$userToBill->LoadUser($_POST['selectedUser']);
} else {
	$userToBill->LoadUser($authenticate->getAuthenticatedUser()->GetUserId());
}

?>

<h3>User Billing</h3>

<div class="panel panel-info">
	<div class="panel-body">
		<p>Your instrument usage bill is reported below. Billing is charged on a monthly cycle</p>
	
		<p>Please contact us to report any inconsistencies you find.</p>
	</div>
</div>
<form action="user_billing.php" method="POST" class="form-inline well">
	<div class="form-group">
		<select name="selectedUser" class="form-control" id="selectUser">
			<?php
			if (empty($selectableUsersList)) {
				echo "<option value=" . $userToBill->GetUserId() . ">" . $userToBill->GetUserName() . "</option>";
			} else {
				foreach ($selectableUsersList as $id => $availUser) {
					echo "<option value=" . $availUser['id'];
					if ($userToBill->GetUserId() == $availUser['id']) {
						echo " SELECTED ";
					}
					echo ">" . $availUser['user_name'] . "</option>";
				}
			}
			?>
		</select>
	</div>
	<div class="form-group">
		<select name="monthSelected" class="form-control" id="selectMonth">
			<?php
			$availableBillingMonths = $bills->GetAvailableBillingMonths();
			foreach ($availableBillingMonths as $id => $charge) {
				echo "<option value=\"" . $charge['month'] . " " . $charge['year'] . "\"";
				if ($charge['month'] == $month && $charge['year'] == $year) {
					echo " SELECTED";
				}
				echo ">" . $charge['mon_yr'] . "</option>";
			}

			?>
		</select>
	</div>
	<div class="form-group">
		<input type="submit" name="selectUserDate" value="View Billing" class="btn btn-primary">
	</div>
</form>
<div class="panel panel-default">
	<div class="panel-heading">
		<h4>Account Information</h4>
	</div>
	<div class="panel-body">
		<table class="table table-striped">
			<tr>
				<th>Netid: </th>
				<td><?php echo $userToBill->GetUserName();?></td>
			</tr>
			<tr>
				<th>Name:</th>
				<td><?php echo $userToBill->GetFirst()." ".$userToBill->GetLast();?></td>
			</tr>
			<tr>
				<th>E-Mail:</th>
				<td><?php echo $userToBill->GetEmail();?></td>
			</tr>
		</table>
	</div>
</div>

<?php
foreach ($rateTypesList as $rateTypeId => $rateTypeName) { ?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h4><?php echo $rateTypeName;?> Billing</h4>
	</div>
	<div class="panel-body">
		<table class="table table-striped table-hover">
			<tr>
				<th>Session id</th>
				<th>Date</th>
				<th>CFOP</th>
				<th>Equipment</th>
				<th>Usage(hrs)</th>
				<th>Rate</th>
				<th>Total</th>
			</tr>
			<?php
				$i = 0;
				$bills->setUserId($userToBill->GetUserId());
				if ($rateTypeId == Bills::MONTHLY_RATE) {
					$bills->setGroupBy(Bills::GROUP_DEVICE);
				}
				$monthCharges = $bills->GetMonthCharges($year, $month, $rateTypeId);

				foreach ($monthCharges as $id => $charge) {
					$rate = $charge['rate'];

					echo "<tr>
						<td>" . $charge['id'] . "</td>
						<td>" . $charge['start'] . "</td>
						<td>" . UserCfop::formatCfop($charge['cfop']) . "</td>
						<td>" . $charge['full_device_name'] . "</td>
						<td>" . round(($charge['elapsed'] / 60), 2) . "</td>
						<td>$" . round(($rate * 60), 2) . "</td>
						<td>$" . number_format($bills->CalcTotal($charge['elapsed'],$rateTypeId,$rate,$charge['min_use_time']),2) . "</td>
					</tr>";
				}
				?>
		</table>
	</div>
</div>
<script type="text/javascript">
	$('#selectUser').select2({'width':'element'});
	$('#selectMonth').select2({'width':'element'});
</script>
<?php
}
require_once 'includes/footer.inc.php';
?>
