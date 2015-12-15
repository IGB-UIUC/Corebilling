<?php
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	
	include('includes/initializer.php');
	
	include 'includes/authenticate.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
		<link href="css/fullcalendar.css" rel='stylesheet' />
		<link href="css/fullcalendar.print.css" rel='stylesheet' media='print' />
		<link href="css/jquery.dataTables.css" rel='stylesheet' />
<!-- 		<link href="css/dataTables.tableTools.min.css" rel='stylesheet' /> -->
		<link href="css/datepicker.css" rel='stylesheet' />
<!-- 		<link href="css/jquery.dataTables.css"  rel="stylesheet" /> -->
<!-- 		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/r/bs/jszip-2.5.0,pdfmake-0.1.18,dt-1.10.9,b-1.0.3,b-html5-1.0.3,b-print-1.0.3/datatables.css"/> -->
<!-- 		<link href="css/dataTables.tableTools.min.css"  rel="stylesheet" /> -->
		<link href="css/main.inc.css" rel="stylesheet" />
		
		<script src='js/jquery/jquery-1.11.1.min.js'></script>
		<script src='js/jquery/jquery-ui.js'></script>
<!-- 		<script src='js/jquery/jquery.dataTables.min.js'></script> -->
		<script type="text/javascript" src="https://cdn.datatables.net/r/bs/jszip-2.5.0,pdfmake-0.1.18,dt-1.10.9,b-1.0.3,b-html5-1.0.3,b-print-1.0.3/datatables.js"></script>
<!-- 		<script src='js/jquery/dataTables.tableTools.min.js'></script> -->
		<script src='js/jquery/fnDisplayRow.js'></script>
		<script src='js/full_calendar/moment.min.js'></script>
		<script src='js/full_calendar/fullcalendar.min.js'></script>
		<script src='js/bootstrap/bootstrap.min.js'></script>
		<script src='js/bootstrap/bootstrap-datepicker.js'></script>
		<script src='js/highcharts/highcharts.js'></script>
		<script src='js/highcharts/exporting.js'></script>
		<script src='js/excel/excellentexport.min.js'></script>
		
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>IGB Instrument Usage Page</title>
	</head>
	<body>
	
		<nav class="navbar navbar-inverse navbar-static-top">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#archive-accounting-nav-collapse" aria-expanded="false">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<div class="navbar-brand">
						<?php echo PAGE_TITLE; ?>
					</div>
				</div>
				<div class="collapse navbar-collapse" id="archive-accounting-nav-collapse">
					<a type="button" class="btn btn-danger btn-sm navbar-btn navbar-right hidden-xs" style="margin-right:0" href="logout.php">Logout</a>
					<a type="button" class="btn btn-danger btn-sm btn-block visible-xs" style="margin-bottom:7px" href="logout.php">Logout</a>
				</div>
			</div>
		</nav>
		
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-10 col-md-push-2">