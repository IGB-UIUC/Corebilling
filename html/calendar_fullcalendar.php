<?php
require_once 'includes/header.inc.php';
$device = new Device($sqlDataBase);
$access = $accessControl->GetPermissionLevel($authenticate->getAuthenticatedUser()->GetUserId(), AccessControl::RESOURCE_PAGE, $pages->GetPageId('Statistics'));
if($access == AccessControl::PERM_DISALLOW){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}

if (isset ($_POST ['deviceSelected'])) {
	if($_POST['deviceSelected']==-1){
		$device->SetDeviceId(-1);
	} else {
		$device->LoadDevice($_POST['deviceSelected']);
	}
}

?>
<div class="alert alert-info">
	<h4>Reservation Calendar</h4>
	<p>Cancellations should be made at least 24 hours in advance.</p>
	<p>Please select a device from the list to view its availability calendar.</p>
	<p>To create a reservation date click on the day you would like to reserve the device for, use < > arrows to select the month.</p>
	<p>To select the reservation time drag click along the hour scale to select a time box.</p>
</div>
<div class="well clearfix">
	<div class="pull-right">
		<form class="form-inline" method="post" action="report.php">
			<input type="hidden" name="month" id="excelmonth"/>
			<input type="hidden" name="year" id="excelyear"/>
			<input type="hidden" name="user_id" value="<?php echo $authenticate->getAuthenticatedUser()->GetUserId(); ?>"/>
			<input type="hidden" name="device_id" value="<?php echo $device->GetDeviceId(); ?>"/>
			<input type="hidden" name="training" value="<?php echo isset($_POST['filterTraining'])?1:0; ?>"/>
			<select name="report_type" class="form-control">
				<option value="xls">Excel 2003</option>
				<option value="xlsx" selected>Excel 2007</option>
				<option value="csv">CSV</option>
			</select>
			<input class="btn btn-primary" type="submit" name="create_cal_report" value="Download Spreadsheet"/>
		</form>
	</div>
	<form action="calendar_fullcalendar.php" method="POST" class="form-inline" name="calform">
		<div class="form-group">
			<select name="deviceSelected" class="form-control" onChange='document.calform.submit();'>
				<?php if($access==AccessControl::PERM_ADMIN){?>
				<option value="-1">Missed Reservations</option>
				<?php } ?>
				<option value=0 <?php if(!isset($_POST['deviceSelected']) || $_POST['deviceSelected']==0) echo 'selected';?>>My Reservations</option>
				<?php
				$deviceList = $device->GetDevicesList();
				foreach ($deviceList as $id => $availDevices) {
					if ($accessControl->GetPermissionLevel($authenticate->getAuthenticatedUser()->GetUserId(), AccessControl::RESOURCE_DEVICE, $availDevices['id'])) {
						echo "<option value=" . $availDevices ['id'];
						if ($availDevices['id'] == $device->GetDeviceId()) {
							echo " SELECTED";
						}
						echo ">" . $availDevices ['full_device_name'] . "</option>";
					}
				}
				?>
			</select>
		</div>
		<?php if ($access == AccessControl::PERM_ADMIN) { ?>
		<div class="checkbox">
			<label><input type="checkbox" name="filterTraining" onChange='document.calform.submit();' <?php if(isset($_POST['filterTraining'])){echo 'checked';} ?>> Filter Training</label>
		</div>
		<?php } ?>
	</form>
</div>
<script>

$(document).ready(function () {

	$('#calendar').fullCalendar({
		editable: true,
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		},
		events: {
			url: 'calendar_api.php',
			type: 'POST',
			allDayDefault: false,
			data: {
				action: 'get_events',
				device_id: '<?php echo $device->GetDeviceId(); ?>',
				user_id: '<?php echo $authenticate->getAuthenticatedUser()->GetUserId(); ?>',
				key: '<?php echo $authenticate->getAuthenticatedUser()->GetSecureKey(); ?>',
				training: '<?php echo isset($_POST['filterTraining'])?1:0; ?>'
			}
		},
		selectable: true,
		selectHelper: true,
		eventRender: function (event, element) {
			if(event.missed){
				element.find('.fc-title').append(' <span class="glyphicon glyphicon-alert"></span>');
			}
		},
		loading: function (isLoading,view){
			// Display loading gif
			if(isLoading){
				$("<img class='loading-gif' src='images/loading-gif.gif'>").insertAfter(".fc-today-button");
			} else {
				$(".loading-gif").remove();
			}
		},
		viewRender: function(view,element){
			if(view.name=="month"){
				// Update month, year inputs for excel button
				$("#excelmonth").val( moment(view.start).add(7,'days').format('MM') );
				$("#excelyear").val( moment(view.start).add(7,'days').format('YYYY') );
			} else {
				// Update month, year inputs for excel button
				$("#excelmonth").val( view.start.format('MM') );
				$("#excelyear").val( view.start.format('YYYY') );
			}
		},
		select: function (start, end) {
			console.log("create new",start,end);
			if (start.hasTime() && end.hasTime()) {
				if (<?php echo $device->GetDeviceId(); ?> > 0) {
					//alert('event clicked');
					var rangeString = start.format('HH:mm:ss') + ' - ' + end.format('HH:mm:ss');
					$('#modifyReservationModal #reservationId').val("0");
					//$('#modifyReservationModal #reservationDescription').val(calEvent.description);
					$('#modifyReservationModal #reservationStart').val(start.format("YYYY-MM-DD HH:mm:ss"));
					$('#modifyReservationModal #reservationEnd').val(end.format("YYYY-MM-DD HH:mm:ss"));
					$('#modifyReservationModal #reservationRange').text(rangeString);
					$('#modifyReservationModal #reservationDevice').text("<?php echo $device->GetFullName(); ?>");
					$('#modifyReservationModal #reservationUsername').text("<?php echo $authenticate->getAuthenticatedUser()->GetUserName(); ?>");
					$('#modifyReservationModal #deleteReservation').hide();
					<?php
					if($access == AccessControl::PERM_ADMIN)
					{
					?>
					$('#modifyReservationModal #trainingFormGroup').show();
					$('#modifyReservationModal #repeatFormGroup').show();

					<?php
					}
					?>
					$('#modifyReservationModal').appendTo("body").modal('show');

					$('#calendar').fullCalendar('unselect');
				} else {
					alert('No device selected: Please select a device calendar.');
					$('#calendar').fullCalendar('unselect');
					$('#calendar').fullCalendar('refetchEvents');
				}
			}
		},
		eventClick: function (calEvent, jsEvent, view) {

			//alert('event clicked');
			var rangeString = calEvent.start.format('HH:mm:ss') + ' - ' + calEvent.end.format('HH:mm:ss');
			$('#modifyReservationModal #reservationId').val(calEvent.id);
			$('#modifyReservationModal #reservationDescription').val(calEvent.description);
			$('#modifyReservationModal #reservationStart').val(calEvent.start.format("YYYY-MM-DD HH:mm:ss"));
			$('#modifyReservationModal #reservationEnd').val(calEvent.end.format("YYYY-MM-DD HH:mm:ss"));
			$('#modifyReservationModal #reservationRange').text(rangeString);
			$('#modifyReservationModal #reservationDevice').text(calEvent.device_name);
			$('#modifyReservationModal #reservationUsername').text(calEvent.username);
			<?php
			if($access == AccessControl::PERM_ADMIN)
			{
			?>
				$('#modifyReservationModal #trainingFormGroup').show();
				if (calEvent.training==1) {

					$('#modifyReservationModal #reservationTraining').attr("checked", true);
				}
				else
				{
					$('#modifyReservationModal #reservationTraining').removeAttr("checked");
				}
			<?php
			}

			?>
			$('#modifyReservationModal #deleteReservation').show();
			$('#modifyReservationModal').appendTo("body").modal('show');


		},
		dayClick: function (date, allDay, jsEvent, view) {
			if (allDay) {
				// Clicked on the entire day

				$('#calendar').fullCalendar('changeView', 'agendaDay');
				$('#calendar').fullCalendar('gotoDate', date);
			}
		},
		eventDrop: function (event, delta) {
			$.ajax({
				url: 'calendar_api.php',
				data: {
					action: 'update_event_time',
					start: event.start.format("YYYY-MM-DD HH:mm:ss"),
					end: event.end.format("YYYY-MM-DD HH:mm:ss"),
					id: event.id,
					device_id: '<?php echo $device->GetDeviceId(); ?>',
					user_id: '<?php echo $authenticate->getAuthenticatedUser()->GetUserId(); ?>',
					key: '<?php echo $authenticate->getAuthenticatedUser()->GetSecureKey(); ?>'
				},
				type: "POST",
				success: function (json) {
					$('#calendar').fullCalendar('refetchEvents');

				}
			});
		},
		eventResize: function (event) {
			$.ajax({
				url: 'calendar_api.php',
				data: {
					action: 'update_event_time',
					start: event.start.format("YYYY-MM-DD HH:mm:ss"),
					end: event.end.format("YYYY-MM-DD HH:mm:ss"),
					id: event.id,
					device_id: '<?php echo $device->GetDeviceId(); ?>',
					user_id: '<?php echo $authenticate->getAuthenticatedUser()->GetUserId(); ?>',
					key: '<?php echo $authenticate->getAuthenticatedUser()->GetSecureKey(); ?>'
				},
				type: "POST",
				success: function (json) {
					$('#calendar').fullCalendar('refetchEvents');

				}
			});

		},
		eventMouseover: function (event, domEvent) {
			var layer = '<div id="events-layer" class="fc-transparent" style="position:absolute; width:100%; height:100%; top:-1px; text-align:right; z-index:100"></div>';
			$(this).append(layer);
			$("#delbut" + event.id).hide();
			$("#delbut" + event.id).fadeIn(300);
			$("#delbut" + event.id).click(function () {
				$.post("your.php", {eventId: event.id});
				calendar.fullCalendar('refetchEvents');
			});
			$("#edbut" + event.id).hide();
			$("#edbut" + event.id).fadeIn(300);
			$("#edbut" + event.id).click(function () {
				var title = prompt('Current Event Title: ' + event.title + '\n\nNew Event Title: ');

				if (title) {
					$.post("your.php", {eventId: event.id, eventTitle: title});
					$('#calendar').fullCalendar('refetchEvents');
				}
			});
		}
	});

	$('#deleteReservation').on('click', function (e) {
		// We don't want this to act as a link so cancel the link action
		e.preventDefault();

		doDeleteReservation();
	});

	function doDeleteReservation() {
		$("#modifyReservationModal").modal('hide');

		var reservationId = $('#reservationId').val();

		if (reservationId) {

			$.ajax({
				url: "calendar_api.php",
				type: "POST",
				data: {
					action: "delete_event",
					id: reservationId,
					user_id: '<?php echo $authenticate->getAuthenticatedUser()->GetUserId(); ?>',
					key: '<?php echo $authenticate->getAuthenticatedUser()->GetSecureKey(); ?>'
				}
			});
			$('#calendar').fullCalendar('refetchEvents');
		}

	}

	$('#updateReservation').on('click', function (e) {
		// We don't want this to act as a link so cancel the link action
		e.preventDefault();

		doUpdateReservation();
	});

	function doUpdateReservation() {
		$("#modifyReservationModal").modal('hide');

		var reservationId = $('#reservationId').val();
		var description = $('#reservationDescription').val();
		var reservationStart = $('#reservationStart').val();
		var reservationEnd = $('#reservationEnd').val();
		var reservationTraining = $('#reservationTraining').is(":checked")?1:0;
		var reservationRepeatInterval = $('#reservationRepeatInterval').val();
		var reservationRepeat =  $('#reservationRepeat').val();

		if (reservationId) {

			$.ajax({
				url: "calendar_api.php",
				type: "POST",
				data: {
					action: "update_event_info",
					description: description,
					start: reservationStart,
					end: reservationEnd,
					id: reservationId,
					training: reservationTraining,
					device_id: '<?php echo $device->GetDeviceId(); ?>',
					user_id: '<?php echo $authenticate->getAuthenticatedUser()->GetUserId(); ?>',
					key: '<?php echo $authenticate->getAuthenticatedUser()->GetSecureKey(); ?>',
					interval: reservationRepeatInterval,
					repeat: reservationRepeat
				}
			});
			$('#calendar').fullCalendar('refetchEvents');
		}
	}
});

</script>
<div id="calendar"></div>
<!-- Modal -->
<div class="modal fade" id="modifyReservationModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
	 aria-hidden="true">

	<div class="modal-dialog">

		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
						class="sr-only">Close</span></button>
				<h3 class="modal-title" id="myModalLabel"><?php if($device->GetDeviceId()==-1){echo 'Reservation Info';}else{echo 'Edit Reservation';} ?></h3>
			</div>
			<div class="modal-body">
				<form id="editReservationForm" class="form-horizontal">

					<div class="form-group">
						<label class="col-sm-3 control-label">User</label>

						<div class="col-sm-9">
							<div class="controls controls-row" id="reservationUsername" style="margin-top:5px">

							</div>
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-sm-3 control-label">Instrument</label>

						<div class="col-sm-9">
							<div class="controls controls-row" id="reservationDevice" style="margin-top:5px">

							</div>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label" for="reservationDescription">Description</label>

						<div class="col-sm-9">
							<input type="text" class="form-control" value="" name="reservationDescription"
								   id="reservationDescription" <?php if($device->GetDeviceId()==-1){echo 'readonly';} ?>>
							<input type="hidden" name="reservationId" id="reservationId">
							<input type="hidden" name="reservationStart" id="reservationStart">
							<input type="hidden" name="reservationEnd" id="reservationEnd">
						</div>
					</div>

					<div id="trainingFormGroup" class="form-group" style="display: none;">
						<label class="col-sm-3 control-label" for="reservationTraining">Training</label>

						<div class="col-sm-9">
							<input type="checkbox" value="" name="reservationTraining" id="reservationTraining">
						</div>
					</div>

					<div id="repeatFormGroup" class="form-group" style="display: none;">
						<label class="col-sm-3 control-label" for="reservationRepeat">Repeat</label>

						<div class="col-sm-2">
							<select name="reservationRepeat" id="reservationRepeat" class="form-control">
								<?php
								for ($repeat = 0; $repeat <= 30; $repeat++) {
									echo "<option value=" . $repeat . " >" . $repeat . "</option>";
								}
								?>
							</select>
						</div>
						<div class="col-sm-3">
							<select name="reservationRepeatInterval" id="reservationRepeatInterval" class="form-control">
								<option value="1">Daily</option>
								<option value="7">Weekly</option>
								</select>
							</div>
						<div class="col-sm-3">

						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">Range</label>

						<div class="col-sm-9">
							<div class="controls controls-row" id="reservationRange" style="margin-top:5px">

							</div>
						</div>
					</div>

					<input type="hidden" name="reservationId" id="reservationId">
					<input type="hidden" name="reservationStart" id="reservationStart">
					<input type="hidden" name="reservationEnd" id="reservationEnd">
				</form>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<?php if($device->GetDeviceId()!=-1){ ?>
				<button type="submit" id="deleteReservation" class="btn btn-primary">Delete</button>
				<button type="submit" id="updateReservation" class="btn btn-primary">Save</button>
				<?php } ?>
			</div>

		</div>

	</div>

</div>

<?php
require_once 'includes/footer.inc.php';
