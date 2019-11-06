<?php
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}

$ldapSearchResults = array();

$selectedUser = new User($db);
$userCfop = new UserCfop($db);
$userDepartment = new Department($db);
$rate = new Rate($db);
$group = new Group($db);

$ldapinfo = null;
if (isset($_REQUEST['user_id'])) {
	$selectedUser->load($_REQUEST['user_id']);
	$ldapinfo = $ldapman->getUser($selectedUser->getUsername());
}

if(isset($_POST['cancel_user'])){
	header('location:list_users.php');
	exit();
}

$message = "";

//If Modified user form
if (isset($_POST['update_user'])) {
	//Update user info
	$selectedUser->setFirstName($_POST['first']);
	$selectedUser->setLastName($_POST['last']);
	$selectedUser->setEmail($_POST['email']);
	$selectedUser->setDepartmentId($_POST['department']);
	if ($login_user->isAdmin()) {
		$selectedUser->setUsername($_POST['user_name']);
		$selectedUser->setRateId($_POST['rate']);
		$selectedUser->setStatusId($_POST['status']);
		$selectedUser->setRoleId($_POST['user_role_id']);
		$selectedUser->setGroupIds($_POST['group']);
		$selectedUser->setCertified(isset($_POST['safetyquiz']));
	}

    $demo = $selectedUser->getDemographics();
    $demo->setEdulevel($_POST['edulevel']);
    $demo->setGender($_POST['gender']);
    $demo->setUnderrep($_POST['underrep']);
    $demo->update();

	$_POST['cfop_to_add']=UserCfop::formatCfop($_POST['cfop_to_add']);
	if( $_POST['cfop_to_add']!="---" && $_POST['cfop_to_add']!=$userCfop->loadDefaultCfop($selectedUser->getId()) )
	{
		// Look in old cfops to see if we're reusing an old one
		$cfopList = $selectedUser->getAllCFOPs();
		$foundcfop = false;
		for($i=0;$i<count($cfopList);$i++){
			if(UserCfop::formatCfop($cfopList[$i]['cfop']) == $_POST['cfop_to_add']){
				$foundcfop = true;
				$selectedUser->setDefaultCFOP($cfopList[$i]['id']);
				break;
			}
		}
		// Otherwise, add new cfop
		if(!$foundcfop){
			$selectedUser->addCFOP($_POST['cfop_to_add']);
		}
	}

// 	if(isset($_POST['access'])){
		$deviceList = Device::getAllDevices($db);
		foreach($deviceList as $deviceInfo){
			if(isset($_POST['access']) && array_key_exists($deviceInfo['id'],$_POST['access'])){
				if(!$selectedUser->hasAccessTo($deviceInfo['id'])){
					if(LDAPMAN_API_ENABLED){
						$ldapman->addGroupMember(LDAPMAN_GROUP_PREFIX.$deviceInfo['device_name'],$selectedUser->getUsername());
					}
					$selectedUser->giveAccessTo($deviceInfo['id']);
				}
			} else {
				if($selectedUser->hasAccessTo($deviceInfo['id'])){
					if(LDAPMAN_API_ENABLED){
						$ldapman->removeGroupMember(LDAPMAN_GROUP_PREFIX.$deviceInfo['device_name'],$selectedUser->getUsername());
					}
					$selectedUser->removeAccessTo($deviceInfo['id']);
				}
			}
		}
// 	}

	if($selectedUser->update()){
		$message .= html::success_message("User updated successfully");
	} else {
		$error = $db->errorInfo();
		$message .= html::error_message("User update failed: ".$error[2]);
	}

}

// Submitted new cfop
if (isset($_POST['add_cfop'])) {
	$selectedUser->addCFOP($_POST['cfop_to_add']);
}

// Submitted New User
if (isset($_POST['create_user'])) {
	if(User::exists($db,$_POST['user_name'])){
		$message .= html::error_message("User ".$_POST['user_name']." already exists in database.");
	} else {
		$selectedUser->create($_POST['user_name'], $_POST['first'], $_POST['last'], $_POST['email'], $_POST['department'], $_POST['rate'], $_POST['status'], $_POST['user_role_id'], isset($_POST['safetyquiz']));
		$selectedUser->setGroupIds($_POST['group']);
		$selectedUser->addCFOP($_POST['cfop_to_add']);
		if(isset($_POST['access'])){
			$deviceList = Device::getAllDevices($db);
			foreach($deviceList as $deviceInfo){
				if(array_key_exists($deviceInfo['id'],$_POST['access'])){
					$selectedUser->giveAccessTo($deviceInfo['id']);
				}
			}
		}
		$selectedUser->update();

		$demo = $selectedUser->getDemographics();
		$demo->setEdulevel($_POST['edulevel']);
		$demo->setGender($_POST['gender']);
		$demo->setUnderrep($_POST['underrep']);
		$demo->update();

		$_REQUEST['user_id'] = $selectedUser->getId();
		$message .= html::success_message("User ".$_POST['user_name']." added to database.");
        $ldapinfo = $ldapman->getUser($selectedUser->getUsername());
	}
}

if (isset($_REQUEST['user_id'])) {
	$selectedUser->load($_REQUEST['user_id']);
}
?>

<h3><?php echo $selectedUser->getId()>0 ? 'Edit':'Add';?> User</h3>
<?php
	if ($selectedUser->getId()>0 && $ldapinfo == null){
		echo html::error_message("This user does not have an IGB account. Any changes made to their device access will not take effect. Please make sure to get their IGB account created before making changes here.","No IGB Account");
	}
?>
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
										<input name="user_name" id="user_name" type="text" class="form-control" value='<?php echo $selectedUser->getUsername(); ?>'>
										<input type="hidden" name="user_id" value="<?php if(isset($_REQUEST['user_id'])){ echo $_REQUEST['user_id'];} ?>"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="editUser">First</label>
									<div class="col-sm-10">
										<input name="first" id="first" type="text" class="form-control" value='<?php echo $selectedUser->getFirstName(); ?>'>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="editUser">Last</label>
									<div class="col-sm-10">
										<input name="last" id="last" type="text" class="form-control" value='<?php echo $selectedUser->getLastName(); ?>'>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="editUser">Mail</label>
									<div class="col-sm-10">
										<input name="email" id="email" type="email" class="form-control" value='<?php echo $selectedUser->getEmail(); ?>'>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="editUser">Depart.</label>
									<div class="col-sm-10">
										<select name="department" class="form-control" id="depart-select">
											<option value=""></option>
											<?php
											$departmentsList = Department::getAllDepartments($db);
											foreach ($departmentsList as $departmentInfo) {
												echo "<option value=" . $departmentInfo['id'];
												if ($departmentInfo['id'] == $selectedUser->getDepartmentId()) {
													echo " SELECTED";
												}
												echo " >" . $departmentInfo['department_name'] . "</option>";
											}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label">CFOP</label>
									<div class="col-sm-10">
										<input type="text" class="form-control" id="cfop" name="cfop_to_add" placeholder="1-xxxxxx-xxxxxx-xxxxxx" value="<?php if($selectedUser->getId()>0){echo UserCfop::formatCfop($selectedUser->getDefaultCFOP());}?>">
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-horizontal">
								<div class="form-group">
									<label class="col-sm-2 control-label" for="editUser">Rate</label>
									<div class="col-sm-10">
										<select name="rate" class="form-control">
											<?php

											$listRates = Rate::getAllRates($db);
											foreach ($listRates as $id => $rate) {
												echo "<option value=" . $rate['id'];
												if ($rate['id'] == $selectedUser->getRateId()) {
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
										<select name="group[]" class="form-control" multiple>
											<?php
											$listGroups = Group::getAllGroups($db);
											$userGroups = $selectedUser->getGroupIds();
											foreach ($listGroups as $id => $groupToSelect) {
												echo "<option value=" . $groupToSelect['id'];
												if (in_array($groupToSelect['id'], $userGroups)) {
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
											$userRolesList = User::getUserRoles($db);
											foreach ($userRolesList as $userRole) {
												echo "<option value=" . $userRole['id'];
												if ($selectedUser->getRoleId() == $userRole['id']) {
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
											$statusList = User::getUserStatusList($db);

											foreach ($statusList as $usersStatus) {
												echo "<option value=" . $usersStatus['id'];
												if ($usersStatus['id'] == $selectedUser->getStatusId()) {
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
											<label><input type="checkbox" name="safetyquiz" <?php if($selectedUser->isCertified()){ echo " checked";} ?>></label>
										</div>
									</div>
								</div>
								<?php if ($selectedUser->getId() > 0) { ?>
								<div class="form-group" style="margin-bottom:0">
									<label class="col-sm-2 control-label" for="editUser">Created</label>
									<div class="col-sm-10">
										<h5><?php echo $selectedUser->getDateAdded();?></h5>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label" for="editUser">Last Login</label>
									<div class="col-sm-10">
										<h5><?php echo $selectedUser->getLastLogin();?></h5>
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
                    <h4>Demographic Info</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="form-horizontal">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="edulevel-select">Education Level</label>
                                    <div class="col-sm-9">
                                        <select name="edulevel" class="form-control" id="edulevel-select">
                                            <option value="">No response</option>
                                            <?php
                                                foreach (UserDemographics::allEduLevels() as $eduLevel){
                                                    $selected = ($selectedUser->getDemographics()->getEdulevel() == $eduLevel)?'selected':'';
                                                    echo "<option value='$eduLevel' $selected>$eduLevel</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="underrep-select">Underrepresented</label>
                                    <div class="col-sm-9">
                                        <select name="underrep" class="form-control" id="underrep-select">
                                            <option value="">No response</option>
                                            <?php
                                                foreach(UserDemographics::allUnderrepOptions() as $option){
                                                    $selected = ($selectedUser->getDemographics()->getUnderrep() == $option)?'selected':'';
                                                    echo "<option value='$option' $selected>$option</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="gender-select">Gender</label>
                                    <div class="col-sm-9">
                                        <select name="gender" class="form-control" id="gender-select">
                                            <option value="">No response</option>
                                            <?php
                                                foreach (UserDemographics::allGenders() as $gender){
                                                    $selected = ($selectedUser->getDemographics()->getGender() == $gender)?'selected':'';
                                                    echo "<option value='$gender' $selected>$gender</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<div class="row">
		<div class="col-sm-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>Device Access</h4>
				</div>
				<div class="panel-body">
					<div class="row">
						<?php
							if($selectedUser->isAdmin()){
								echo "<div class='col-sm-12'>Admins have access to all devices.</div>";
							} else {
								$deviceList = Device::getAllDevices($db);
								foreach($deviceList as $deviceInfo){
									if($deviceInfo['status_id']==1 || $deviceInfo['status_id']==3){
										$checked="";
										if( $selectedUser->hasAccessTo($deviceInfo['id']) ){
											$checked=" checked='checked'";
										}
										echo "<div class='col-sm-2'><label><input type='checkbox'".$checked." name='access[".$deviceInfo['id']."]'/> ".$deviceInfo['full_device_name']."</label></div>";
									}
								}
							}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php echo $message; ?>

	<div class="form-group">
		<div class="col-sm-12">
			<?php
			if ($selectedUser->getId() > 0) {
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
	$('#depart-select').select2({
		placeholder: "Select a Department"
	});
	$('#copy-button').click(function(e){
		var cfop = $('#cfop').text();
		var $textarea = $('#cfop-copy-area');
		$textarea.val(cfop);
		var showTextArea = true;
		if(document.queryCommandSupported('copy')){
			showTextArea = false;
			$textarea.removeClass('hidden');
			$textarea[0].select();

			try {
				var success = document.execCommand('copy');
			} catch(err) {
				showTextArea = true;
			}

 			$textarea.addClass('hidden');
			window.getSelection().removeAllRanges();
		}
		if(showTextArea){
			$textarea.removeClass('hidden');
		}
		e.preventDefault();
	});

	var seqnum = 0;
	$('#user_name').on('input',function(){
		var $this = $(this);
		seqnum++;
		var currentseqnum = seqnum;
		$.ajax('ldap_user_info.php',{
			data: {'uid':$this.val()},
			method: 'post',
			success: function(data){
				if(seqnum==currentseqnum){
					if(data!=null){
						$('#first').val(data.givenName);
						$('#last').val(data.sn);
						$('#email').val(data.email);
					} else {
						$('#first').val("");
						$('#last').val("");
						$('#email').val("");
					}
				}
			}
		});
	});

</script>
<?php
require_once 'includes/footer.inc.php';
?>
