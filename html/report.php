<?php

include_once 'includes/initializer.php';

if (isset($_POST['create_cal_report'])) {
    $user = new User ($db);
    $user->load($_REQUEST['user_id']);

    //Verify the user is who is is saying he is by comparing the user key from the database to key given to the api
    if ($user->getSecureKey() == $_REQUEST ['key']) {
        // TODO set start and end based on month and year
        $month = $_POST['month'];
        $year = $_POST['year'];
        $type = $_POST['report_type'];

        $start_date = $year . $month . "01";
        try {
            $end_date = (new DateTime($start_date))->format('Y-m-t 23:59:59');
        } catch (Exception $e) {
            die($e->getMessage());
        }

        $events = Reservation::getEventsInRangeForSpreadsheet(
            $db,
            $start_date,
            $end_date,
            $_POST['user_id'],
            $_POST['device_id'],
            $_POST['training']
        );
        $filename = "calendar-" . $month . "-" . $year . "." . $type;
    }

    switch ($type) {
        case 'csv':
            report::create_csv_report($events, $filename);
            break;
        case 'xls':
            report::create_xls_report($events, $filename);
            break;
        case 'xlsx':
            report::create_xlsx_report($events, $filename);
            break;
    }
}