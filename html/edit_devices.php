<?php
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}

$device = new Device($db);
$rate = new Rate($db);
$rateTypes = Rate::getAllRateTypes($db);

if (isset($_POST['device_option'])) {
	$device_option = $_POST['device_option'];
	if ($device_option != 'new') {
		$device->load($device_option);
	}
}

if (isset($_POST['ModifyDevice'])) {
	$device->load($_POST['device_id']);
	$device->setShortName($_POST['dnsName']);
	$device->setFullName($_POST['deviceName']);
	$device->setLocation($_POST['location']);
	$device->setDescription($_POST['description']);
	$device->setStatus($_POST['status']);
	$device->update();
	$ratesArr = $_POST['ratesBox'];

	foreach ($ratesArr as $key => $value) {
		$rateId = $value;
		$rateValue = $_POST["rate-" . $value] / 60;
		$minTime = $_POST["mintime-" . $value];
		$rateTypeId = $_POST["rate_type-" . $value];
		$device->updateRate($rateId, $rateValue, $minTime, $rateTypeId);
	}

}

if (isset($_POST['add_rate'])) {

	$rate->create($_POST['new_rate_name'], $_POST['new_rate_type']);
}

if (isset($_POST['CreateNewDevice'])) {
	$device->create($_POST['dnsName'], $_POST['deviceName'], $_POST['location'], $_POST['description'], $_POST['status']);

}

?>
<h3>Devices Configuration</h3>
<form action="edit_devices.php" method=POST>
	<div class="form-group">
		<?php
		if ($device->getId() > 0) {
			echo "<input name=\"ModifyDevice\" type=\"submit\" class=\"btn btn-primary\" id=\"Modify\" value=\"Modify\">";
		} else {
			echo "  <input name=\"CreateNewDevice\" type=\"submit\" class=\"btn btn-primary\" id=\"Submit\" value=\"Create\" >";
		}
		?>
	</div>
	<div class="row">
		<div class="col-md-6">
			<div class="well form-horizontal">
				<div class="form-group">
					<label class="col-sm-2 control-label" for="editDevice">Device</label>
					<div class="col-sm-6">
						<select name="device_option" class="form-control">
							<option selected value='new'>New Device</option>
							<?php
							$devicesList = Device::getAllDevices($db);
							foreach ($devicesList as $id => $deviceInfo) {
								echo "<option value=" . $deviceInfo["id"];
								if ($device->getId() == $deviceInfo["id"]) {
									echo " selected";
								}
								echo ">" . $deviceInfo["full_device_name"] . "</option>";
							}
							?>
						</select>
					</div>
					<div class="col-sm-3">
						<input name="Select" type="submit" class="btn btn-primary" id="Select" value="Select"/>
					</div>
				</div>
			</div>
			<div class="well form-horizontal">
				<div class="form-group">
					<label class="col-sm-3 control-label" for="editDevice">
						Device ID (
						<?php echo $device->getId(); ?>
						):
					</label>
					<div class="col-sm-9">
						<input name="device_id" type="hidden" value="<?php echo $device->getId(); ?>">
						<input name="dnsName" type="text" value="<?php echo $device->getShortName(); ?>" class="form-control">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label" for="editDevice">Auth. Token:</label>
					<div class="col-sm-9">
						<input type="text" name="auth_key" value="<?php echo $device->getDeviceToken(); ?>" class="form-control" readonly>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label" for="editDevice">Device Name:</label>
					<div class="col-sm-9">
						<input type="text" name="deviceName" class="form-control" value="<?php echo $device->getFullName(); ?>">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label" for="editDevice">Status</label>
					<div class="col-sm-9">
						<select name="status" class="form-control">
							<?php
							$statusList = Device::deviceStatusList($db);
							foreach ($statusList as $id => $statusOption) {
								echo "<option value=" . $statusOption["id"];
								if ($device->getStatus() == $statusOption["id"]) {
									echo " SELECTED";
								}
								echo ">" . $statusOption["statusname"] . "</option>";
							}
							?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label" for="editDevice">Location:</label>
					<div class="col-sm-9">
						<input type="text" name="location" class="form-control" value="<?php echo $device->getLocation(); ?>">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label" for="ldap_group">LDAP Group:</label>
					<div class="col-sm-9">
						<input type="text" name="ldap_group" id="ldap_group" class="form-control" value="<?php echo $device->getLDAPGroup(); ?>">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label" for="editDevice">Notes</label>
					<div class="col-sm-9">
						<textarea name="description" class="form-control"><?php echo $device->getDescription();?></textarea>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="well form-horizontal">
				<div class="form-group">
					<label class="col-sm-2 control-label" for="editDevice">
						Rate
					</label>
					<div class="col-sm-4">
						<input type="text" name="new_rate_name" class="form-control">
					</div>
					<div class="col-sm-4">
						<select name="new_rate_type" class="form-control">
							<?php
							foreach ($rateTypes as $id => $rateTypeInfo)
							{
								echo "<option value=" . $rateTypeInfo['id'].">". $rateTypeInfo['rate_type_name'] . "</option>";
							}
							?>
						</select>
					</div>
					<div class="col-sm-2">
						<input type="submit" class="btn btn-primary" value="Add" name="add_rate">
					</div>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>Device Rates <?php echo $device->getFullName(); ?><h4>
				</div>
				<div class="panel-body">
					<table class="table table-hover">
						<tr>
							<th>Rate Name</th>
							<th>Rate ($)</th>
							<th>Min. Time</th>
							<th>Usage Period</th>
						</tr>
						<?php
						if ($device->getId() > 0) {
							$deviceRates = $device->getRates();
							foreach ($deviceRates as $id => $deviceRateInfo) {
								echo "<tr>
										<td>
											<input type=\"checkbox\" name=\"ratesBox[]\" value=\"" . $deviceRateInfo["rate_id"] . "\" style=\"display:none;\" CHECKED>
											<h5>" . $deviceRateInfo["rate_name"] . ":</h5>
											</td>
											<td>
											<input type=\"text\" value=" . round($deviceRateInfo["rate"] * 60) . " name=\"rate-" . $deviceRateInfo["rate_id"] . "\" size=\"3\" maxlength=\"5\" class=\"form-control\">
											</td>
											<td>
											<input type=\"text\" value=" . $deviceRateInfo["min_use_time"] . " name=\"mintime-" . $deviceRateInfo["rate_id"] . "\" size=\"5\" maxlength=\"5\" class=\"form-control\">
											</td>";
								echo "  <td>
											<select name=\"rate_type-" . $deviceRateInfo["rate_id"] . "\" class=\"form-control\">";
	
								$rateTypeNotSelected = true;
	
								foreach ($rateTypes as $id => $rateTypeInfo)
								{
									echo "<option value=" . $rateTypeInfo['id'];
									if ($rateTypeInfo['id'] == $deviceRateInfo['rate_type_id']) {
										echo " SELECTED";
										$rateTypeNotSelected = false;
	
									}
									echo ">" . $rateTypeInfo['rate_type_name'] . "</option>";
								}
								if ($rateTypeNotSelected) {
									echo "<option value=0 SELECTED>Not Set</option>";
								}
	
								echo "</select>";
								echo "</td></tr>";
							}
						}
						?>
					</table>
				</div>
			</div>
		</div>
	</div>
</form>
<?php
	require_once 'includes/footer.inc.php';