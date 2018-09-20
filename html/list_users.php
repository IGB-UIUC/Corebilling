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

?>

<h3>List Users</h3>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<?php if($login_user->isAdmin()){ ?>
					<a href="edit_users.php" class="btn btn-sm btn-primary pull-right"><span class="glyphicon glyphicon-plus"></span> Add user</a>
				<?php } ?>
				<h4>User Directory</h4>
			</div>
			<div class="body">
				<?php
				$usersFullInfoList = $selectedUser->GetAllUsersFullInfo();
				echo VisualizeData::ListSessionsTable($usersFullInfoList,
					array('Name', 'E-Mail', 'CFOP', 'Group', 'Department', 'Last Login', 'Status', ''),
					array('full_name', 'email', 'cfop', 'group_name', 'department_name', 'last_login', 'status', 'edit'), 'usersTable',0);
				?>
				<script type="text/javascript">
					// Sort by status, then name to show active users first
					$(document).ready(function(){
						usersTable.column(6).search('Active').draw();
						$('<label>Show disabled users <input type="checkbox" id="filteractive" /> &nbsp;</label>').prependTo('#usersTable_filter');
						$('#filteractive').on('change',function(e){
							if(this.checked){
								usersTable.column(6).search('').draw();
							} else {
								usersTable.column(6).search('Active').draw();
							}
						});
						});
					
				</script>
			</div>
		</div>
	</div>
</div>

<?php
require_once 'includes/footer.inc.php';
?>