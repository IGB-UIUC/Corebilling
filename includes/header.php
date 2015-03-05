<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="css/bootstrap.css" rel="stylesheet" type="text/css">
<link href="css/font-awesome.css" rel="stylesheet">
<link href="css/bootstrap-responsive.css" rel="stylesheet" type="text/css">
<link href='css/fullcalendar.css' rel='stylesheet' />
<link href='css/fullcalendar.print.css' rel='stylesheet' media='print' />
<link href='css/datepicker.css' rel='stylesheet' />
<link href="css/jquery.dataTables.css"  rel="stylesheet" />
<link href="css/dataTables.tableTools.min.css"  rel="stylesheet" />
<link href="css/bootstrap.css.map"  rel="stylesheet" />


<style type="text/css">
body {
	padding-top: 60px;
	padding-bottom: 40px;
}
#calendar {
		width: 900px;
		margin: 0 auto;
}
</style>
<script src='js/jquery/jquery-1.11.1.min.js'></script>
<script src='js/jquery/jquery-ui.js'></script>
<script src='js/jquery/jquery.dataTables.min.js'></script>
<script src='js/jquery/dataTables.tableTools.min.js'></script>
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

<div class="container">
<div class="navbar navbar-default" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#"><?php echo PAGE_TITLE;?></a>
        </div>
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="../navbar-fixed-top/">
                        <?php
                        include('includes/logout.php');
                        ?>
                    </a>
                </li>
            </ul>
        </div><!--/.nav-collapse -->
    </div><!--/.container-fluid -->
</div>
<div class="row-fluid">