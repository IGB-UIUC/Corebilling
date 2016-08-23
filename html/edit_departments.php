<?php
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}

$department = new Department($sqlDataBase);

if (isset($_POST['Submit'])) {
	$departmentName = $_POST['department_name'];
	$description = $_POST['description'];
	$departmentId = $_POST['department_id'];

	if ($department->Exists($departmentName)) {
		$warnings .= "Department name already exists.";
	} else {
		$department->AddDepartment($departmentName,$description);

	}

}

if (isset($_POST['Modify'])) {
	$departmentName = $_POST['department_name'];
	$departmentId = $_POST['department_id'];
	$description = $_POST['description'];

	$department->LoadDepartment($departmentId);
	$department->setDepartmentName($departmentName);
	$department->setDescription($description);
	$department->UpdateDepartment();
}


if (isset($_POST['Select'])) {
	$departmentId = $_POST['selectDepartment'];
	$department->LoadDepartment($departmentId);

	$announce = "<h4>Modify Department:</h4>Modify department details, click modify to apply changes.";
	$newDepartmentBtn = ' <input name="Reset" type="submit" class="btn btn-primary" id="reset" value="Reset" >';
}
?>

<h3>Edit Departments</h3>

<form action="edit_departments.php" method="POST">
	<div class="form-group">
		<?php
		echo "<input name=\"department_id\" type=\"hidden\" value=\"" . $department->getDepartmentId() . "\">";
		if ($department->getDepartmentId() != 0) {
			echo '<input name="Modify" type="submit" class="btn btn-primary" id="Modify" value="Modify">';
		} else {
			echo '<input name="Submit" type="submit" class="btn btn-primary" id="Submit" value="Create" >  <input name="Reset" type="submit" class="btn btn-primary" id="reset" value="Reset" >';
		}
		?>
	</div>
	<div class="row">
		<div class="col-md-6">
			<div class="well form-horizontal">
				<div class="form-group">
					<label class="col-sm-3 control-label" for="editUser">Department</label>
					<div class="col-sm-5">
						<select name="selectDepartment" class="form-control">
							<?php
							$departmentList = $department->GetDepartmentList();
							echo "<option value=\"0\">New Department</option>";
							foreach ($departmentList as $departmentInfo) {
								echo "<option value=" . $departmentInfo["id"];
								if($departmentInfo['id']==$department->getDepartmentId())
								{
									echo " selected";
								}
								echo ">" . $departmentInfo["department_name"] . "</option>";
							}
							?>
						</select>
					</div>
					<div class="col-sm-3">
						<input name="Select" type="submit" class="btn btn-primary" id="Select" Value="Select"/>
					</div>
				</div>
			</div>
			<div class="well form-horizontal">
				<div class="form-group">
					<label class="col-sm-3 control-label">Name</label>
					<div class="col-sm-9">
						<textarea name="department_name" type="text" class="form-control"><?php echo $department->getDepartmentName(); ?></textarea>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-3 control-label">Description</label>
					<div class="col-sm-9">
						<textarea name="description" class="form-control"><?php echo $department->getDescription(); ?></textarea>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4>Members</h4>
				</div>
				<div class="panel-body">
					<table class="table table-striped table-hover">
						<tr>
							<th>NetId</th>
							<th>Full Name</th>
						</tr>
						<?php
						$members = $department->GetMembers();
						foreach($members as $id=>$member)
						{
							echo "<tr><td>".$member['user_name']."</td><td>".$member['first']." ".$member['last']."</td></tr>";
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