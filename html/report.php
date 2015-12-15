<?php
include_once 'includes/initializer.php';

if(isset($_POST['create_cal_report'])){
	// TODO set start and end based on month and year
	$month = $_POST['month'];
	$year = $_POST['year'];
	$type = $_POST['report_type'];
	
	$start_date = $year.$month."01";
	$end_date = date('Ymd',strtotime('-1 second',strtotime('+1 month',strtotime($start_date))));
	
	$res = new Reservation($sqlDataBase);
	$events = $res->EventsRange($start_date,$end_date,$_POST['user_id'],$_POST['device_id'],$_POST['training']);
	$filename = "calendar-".$month."-".$year.".".$type;
}
else {
	exit;
}
switch($type){
	case 'csv':
		report::create_csv_report($events,$filename);
		break;
	case 'xls':
		report::create_xls_report($events,$filename);
		break;
	case 'xlsx':
		report::create_xlsx_report($events,$filename);
		break;
}
?>