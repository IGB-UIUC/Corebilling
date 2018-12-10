</div> <!-- col-10 -->
<?php if ($authenticate->isVerified()) { ?>
			<div class="col-md-2 col-md-pull-10">
				<ul class="nav nav-pills nav-stacked">
					<li><a href="news.php">News</a></li>
					<li><a href="user_billing.php">User Bill</a></li>
					<li><a href="calendar_fullcalendar.php">Calendar</a>
					<li><a href="in_use.php">Device Status</a></li>
					<?php if ($login_user->isAdmin()){ ?>
						  
					<hr>
					<li><a href="list_users.php">Users</a></li>
					<li><a href="edit_groups.php">Groups</a></li>
					<li><a href="edit_departments.php">Departments</a></li>
					<li><a href="edit_devices.php">Devices</a></li>
					<hr>
					<li><a href="facility_billing.php">Facility Billing</a></li>
					<li><a href="facility_demographics.php">Facility Demographics</a></li>
					<li><a href="active_users.php">Active Users</a></li>
					<li><a href="reservation_use.php">Reservation Usage Data</a></li>
					<li><a href="usage_compare.php">Reservation-Usage Comparison</a></li>
<!-- 					<li><a href="dev_statistics.php">Statistics</a></li> -->
						  
					<?php } ?>
				 </ul>
			</div>
			<?php } ?>
		</div> <!-- row -->
		<div class="row">
			<div class='col-sm-12' style='text-align: center; padding: 15px 0'>
				<em>&copy 2015 University of Illinois Board of Trustees</em>
			</div>
		</div>
	</body>
</html>
