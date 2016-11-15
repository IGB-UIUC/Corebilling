<?php
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}

$ldapSearchResults = array();

$selectedUser = new User($sqlDataBase);
$userDepartment = new Department($sqlDataBase);
$rate = new Rate($sqlDataBase);
$group = new Group($sqlDataBase);
$device = new Device($sqlDataBase);

if (isset($_REQUEST['user_id'])) {
	$selectedUser->LoadUser($_REQUEST['user_id']);
}

//If Modified user form
if (isset($_POST['update_user'])) {
	//Update user info
	$selectedUser->SetFirst($_POST['first']);
	$selectedUser->SetLast($_POST['last']);
	$selectedUser->SetEmail($_POST['email']);
	$selectedUser->SetDepartmentId($_POST['department']);
	if ($login_user->isAdmin()) {
		$selectedUser->SetUserName($_POST['user_name']);
		$selectedUser->SetRateId($_POST['rate']);
		$selectedUser->SetStatusId($_POST['status']);
		$selectedUser->SetUserRoleId($_POST['user_role_id']);
		$selectedUser->SetGroupId($_POST['group']);
		$selectedUser->SetCertified(isset($_POST['safetyquiz']));
	}
	if(isset($_POST['user_cfop_id']))
	{
		$selectedUser->SetDefaultCfop($_POST['user_cfop_id']);
	}

	if(isset($_POST['access'])){
		$deviceList = $device->GetDevicesList();
		foreach($deviceList as $deviceInfo){
			if(array_key_exists($deviceInfo['id'],$_POST['access'])){
				$accessControl->SetAccess(AccessControl::RESOURCE_DEVICE, $deviceInfo['id'], AccessControl::PARTICIPANT_USER, $selectedUser->GetUserId(), AccessControl::PERM_ALLOW);
				if(LDAPMAN_API_ENABLED){
					$ldapman->addGroupMember(LDAPMAN_GROUP_PREFIX.$deviceInfo['device_name'],$selectedUser->GetUserName());
				}
			} else {
				$accessControl->SetAccess(AccessControl::RESOURCE_DEVICE, $deviceInfo['id'], AccessControl::PARTICIPANT_USER, $selectedUser->GetUserId(), AccessControl::PERM_DISALLOW);
				if(LDAPMAN_API_ENABLED){
					$ldapman->removeGroupMember(LDAPMAN_GROUP_PREFIX.$deviceInfo['device_name'],$selectedUser->GetUserName());
				}
			}
		}
	}

	$selectedUser->UpdateUser();

}

// Submitted new cfop
if (isset($_POST['add_cfop'])) {
	$selectedUser->AddCfop($_POST['cfop_to_add']);
}

// Submitted New User
if (isset($_POST['create_user'])) {
	$selectedUser->CreateUser($_POST['user_name'], $_POST['first'], $_POST['last'], $_POST['email'], $_POST['department'], $_POST['group'], $_POST['rate'], $_POST['status'], $_POST['user_role_id'], $_POST['group']);
	$selectedUser->AddCfop($_POST['cfop_to_add']);
	if(isset($_POST['access'])){
		$deviceList = $device->GetDevicesList();
		foreach($deviceList as $deviceInfo){
			if(array_key_exists($deviceInfo['id'],$_POST['access'])){
				$accessControl->SetAccess(AccessControl::RESOURCE_DEVICE, $deviceInfo['id'], AccessControl::PARTICIPANT_USER, $selectedUser->GetUserId(), AccessControl::PERM_ALLOW);
			} else {
				$accessControl->SetAccess(AccessControl::RESOURCE_DEVICE, $deviceInfo['id'], AccessControl::PARTICIPANT_USER, $selectedUser->GetUserId(), AccessControl::PERM_DISALLOW);
			}
		}
	}
	$selectedUser->UpdateUser();
	$_REQUEST['user_id'] = $selectedUser->GetUserId();
}

if (isset($_REQUEST['user_id'])) {
	$selectedUser->LoadUser($_REQUEST['user_id']);
}
?>

<h3>Edit User</h3>
<form action="edit_users.php" method=POST>
	<div class="row">
		<div class="col-sm-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>User Info</h4>
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-horizontal">
								<div class="form-group">
									<label class="col-sm-2 control-label" for="editUser">Netid</label>
									<div class="col-sm-10">
										<input name="user_name" type="text" class="form-control" value='<?php echo $selectedUser->GetUserName(); ?>'>
										<input type="hidden" name="user_id" value="<?php echo $_REQUEST['user_id'];?>"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="editUser">First</label>
									<div class="col-sm-10">
										<input name="first" type="text" class="form-control" value='<?php echo $selectedUser->GetFirst(); ?>'>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="editUser">Last</label>
									<div class="col-sm-10">
										<input name="last" type="text" class="form-control" value='<?php echo $selectedUser->GetLast(); ?>'>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="editUser">Mail</label>
									<div class="col-sm-10">
										<input name="email" type="email" class="form-control" value='<?php echo $selectedUser->GetEmail(); ?>'>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="editUser">Depart.</label>
									<div class="col-sm-10">
										<select name="department" class="form-control" id="depart-select">
											<?php
											$departmentsList = $userDepartment->GetDepartmentList();
											foreach ($departmentsList as $departmentInfo) {
												echo "<option value=" . $departmentInfo['id'];
												if ($departmentInfo['id'] == $selectedUser->GetDepartmentId()) {
													echo " SELECTED";
												}
												echo " >" . $departmentInfo['department_name'] . "</option>";
											}
											?>
										</select>
									</div>
								</div>
								<?php if($selectedUser->GetUserId() > 0){ ?>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="editUser">CFOP</label>
									<div class="col-sm-10">
										<select name="user_cfop_id" class="form-control">
											<?php
											$userCfopList = $selectedUser->ListCfops($selectedUser->GetUserId());
											foreach ($userCfopList as $id => $cfopCodeInfo) {
												echo "<option value=" . $cfopCodeInfo['id'];
												if ($cfopCodeInfo['default_cfop']) {
													echo " SELECTED";
												}
												echo ">" . UserCfop::formatCfop($cfopCodeInfo['cfop']) . "</option>";
											}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<div class="col-sm-2"></div>
									<div class="col-sm-8">
										<input type="text" class="form-control" name="cfop_to_add" placeholder="1-xxxxxx-xxxxxx-xxxxxx">
									</div>
									<div class="col-sm-2">
										<input type="submit" name="add_cfop" value="Add CFOP" class="btn btn-primary btn-block">
									</div>
								</div>
								<?php } else { ?>
								<div class="form-group">
									<label class="col-sm-2 control-label">CFOP</label>
									<div class="col-sm-10">
										<input type="text" class="form-control" name="cfop_to_add" placeholder="1-xxxxxx-xxxxxx-xxxxxx">
									</div>
								</div>
								<?php } ?>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-horizontal">
								<div class="form-group">
									<label class="col-sm-2 control-label" for="editUser">Rate</label>
									<div class="col-sm-10">
										<select name="rate" class="form-control">
											<?php
					
											$listRates = $rate->GetRates();
											foreach ($listRates as $id => $rate) {
												echo "<option value=" . $rate['id'];
												if ($rate['id'] == $selectedUser->GetRateId()) {
													echo " SELECTED";
												}
												echo ">" . $rate['rate_name'] . "</option>";
											}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="editUser">Group</label>
									<div class="col-sm-10">
										<select name="group" class="form-control">
											<?php
											$listGroups = $group->GetGroupsList();
											foreach ($listGroups as $id => $groupToSelect) {
												echo "<option value=" . $groupToSelect['id'];
												if ($selectedUser->GetGroupId() == $groupToSelect['id']) {
													echo " SELECTED";
												}
												echo ">" . $groupToSelect['group_name'] . "</option>";
											}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="editUser">Role</label>
									<div class="col-sm-10">
										<select name="user_role_id" class="form-control">
											<?php
											$userRolesList = $selectedUser->GetUserRoles();
											foreach ($userRolesList as $userRole) {
												echo "<option value=" . $userRole['id'];
												if ($selectedUser->GetUserRoleId() == $userRole['id']) {
													echo " SELECTED";
												}
												echo ">" . $userRole['role_name'] . "</option>";
											}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="editUser">Status</label>
									<div class="col-sm-10">
										<select name="status" class="form-control">
											<?php
											$userStatus = 2;
											$queryUsersStatus = "SELECT statusname,id FROM status WHERE type=" . $userStatus;
					
											foreach ($sqlDataBase->query($queryUsersStatus) as $usersStatus) {
												echo "<option value=" . $usersStatus['id'];
												if ($usersStatus['id'] == $selectedUser->GetStatusId()) {
													echo " SELECTED";
												}
												echo ">" . $usersStatus['statusname'] . "</option>";
											}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label">Safety Quiz</label>
									<div class="col-sm-10">
										<div class="checkbox">
											<label><input type="checkbox" name="safetyquiz"></label>
										</div>
									</div>
								</div>
								<?php if ($selectedUser->GetUserId() > 0) { ?>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="editUser">Created</label>
									<div class="col-sm-10">
										<h5><?php echo $selectedUser->GetDateAdded();?></h5>
									</div>
								</div>
								<?php } ?>
							</div>
						</div> <!-- .col-sm-6 -->
					</div> <!-- .row -->
				</div>
			</div> <!-- .panel -->
		</div>
	</div> <!-- .row -->
	<div class="row">
		<div class="col-sm-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>Device Access</h4>
				</div>
				<div class="panel-body">
					<div class="row">
						<?php
							$deviceList = $device->GetDevicesList();
							foreach($deviceList as $deviceInfo){
								if($deviceInfo['status_id']==1 || $deviceInfo['status_id']==3){
									$checked="";
									if($accessControl->GetPermissionLevel($selectedUser->GetUserId(), AccessControl::RESOURCE_DEVICE, $deviceInfo['id'])){
										$checked=" checked='checked'";
									}
									echo "<div class='col-sm-2'><label><input type='checkbox'".$checked." name='access[".$deviceInfo['id']."]'/> ".$deviceInfo['full_device_name']."</label></div>";
								}
							}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="form-group">
		<div class="col-sm-12">
			<?php
			if ($selectedUser->GetUserId() > 0) {
				echo '<input name="update_user" type="submit" class="btn btn-primary" value="Update User">';
			} else {
				echo '<input name="create_user" type="submit" class="btn btn-primary" value="Create User" >';
			}
			?>
			<input name="cancel_user" type="submit" class="btn btn-default" value="Cancel" />
		</div>
	</div>
	
</form>

<script type="text/javascript">
	$('#user-select').select2();
	$('#depart-select').select2();
</script>
<?php
require_once 'includes/footer.inc.php';
?>