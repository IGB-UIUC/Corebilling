<?php
set_time_limit(20);
include('includes/initializer.php');
if (isset($_REQUEST['action']) && isset($_REQUEST['user_id']) && isset($_REQUEST['key'])) {
    //Load User information for user_id
    $user = new User ($db);
    $user->load($_REQUEST['user_id']);

    //Verify the user is who is is saying he is by comparing the user key from the database to key given to the api
    if ($user->getSecureKey() == $_REQUEST ['key']) {

        //Create reservation object and load reservation info if we are given a reservation id
        $reservation = new Reservation ($db);
        if (isset($_REQUEST['id'])) {
            $reservation->load($_REQUEST ['id']);
        }

        if ($user->isAdmin() // Admins can do anything
            || $user->getId() == $reservation->getUserId() // Users can edit their own res
            || ($_REQUEST['action'] == 'update_event_info' && $_REQUEST['user_id']==$user->getId() && $reservation->getReservationId()==0) // Users can create their own res
            || ($_REQUEST['action'] == 'check_conflicts' && $_REQUEST['res_user_id']==$user->getId()) // Users can check conflicts
            || ($_REQUEST['action']=='get_events' )) // Users can display events
        {
            switch ($_REQUEST['action']) {
                case 'get_events':
                    echo Reservation::getEventsInRangeJSON($db,$_REQUEST['start'], $_REQUEST['end'], $_REQUEST ['user_id'], $_REQUEST ['device_id'],$_REQUEST['training']==1);
                    break;
                case 'add_event':
                    $training = (isset($_REQUEST['training']))?1:0;
                    $dateStart = new DateTime($_REQUEST['start']);
                    $dateEnd = new DateTime($_REQUEST['end']);
                    $reservation->create($_REQUEST['device_id'], $_REQUEST ['user_id'], $dateStart->format('Y-m-d H:i:s'), $dateEnd->format('Y-m-d H:i:s'), $_REQUEST['description'], $training);
//                     TODO implement repeat
                    break;
                case 'delete_event':
                    if($reservation->getMasterReservationId() == null) {
                        $reservationsInRoom = Reservation::getSubEvents($db, $reservation->getReservationId());
                        $reservation->delete();
                        foreach ($reservationsInRoom as $reservationInRoom) {
                            $reservation->load($reservationInRoom['id']);
                            $reservation->delete();
                        }
                    }
                    break;
                case 'update_event_time':
                	if(Reservation::checkEventTime($db,strtotime($_REQUEST['start']), strtotime($_REQUEST['end']), $_REQUEST['id']) != 0){
	                    $reservation->setStart($_REQUEST ['start']);
	                    $reservation->setStop($_REQUEST ['end']);
	                    $reservation->update();

                        // update reservations for other devices in room
                        $reservationsInRoom = Reservation::getSubEvents($db, $reservation->getReservationId());
                        foreach($reservationsInRoom as $reservationInRoom){
                            $reservation->load($reservationInRoom['id']);
                            $reservation->setStart($_REQUEST ['start']);
                            $reservation->setStop($_REQUEST ['end']);
                            $reservation->update();
                        }
	                }
                    break;
                case 'finish_early':
                	if($reservation->isInProgress()){
	                	$reservation->finishEarly();
                	}
                	break;
                case 'update_event_info':
                    $training = (isset($_REQUEST['training']))?$_REQUEST['training']:0;
                    $repeat = (isset($_REQUEST['repeat']))?(int)$_REQUEST['repeat']:0;
                    $interval = (isset($_REQUEST['interval']))?(int)$_REQUEST['interval']:0;
                    $staffNotes = (isset($_REQUEST['staffNotes']))?$_REQUEST['staffNotes']:null;
                    $dateStart = new DateTime($_REQUEST['start']);
                    $dateEnd = new DateTime($_REQUEST['end']);
                    if ($reservation->getReservationId() == 0) {
                        for($i=0; $i<=$repeat; $i++) {
                            $reservation->create($_REQUEST['device_id'], $_REQUEST ['user_id'], $dateStart->format('Y-m-d H:i:s'), $dateEnd->format('Y-m-d H:i:s'), $_REQUEST['description'], $training);
                            if($staffNotes) {
                                $reservation->setStaffNotes($staffNotes);
                                $reservation->update();
                            }
                            if(RESERVE_ROOM){
                                // when enabled, add reservation to other devices in room
                                $devicesInRoom = Device::getDevicesInSameRoom($db, $_REQUEST['device_id']);
                                $masterId = $reservation->getReservationId();
                                foreach ($devicesInRoom as $device){
                                    $reservation->create($device['id'], $_REQUEST['user_id'], $dateStart->format('Y-m-d H:i:s'), $dateEnd->format('Y-m-d H:i:s'), $_REQUEST['description'], $training, $masterId);
                                }
                            }
                            $dateStart->add(new DateInterval("P".($interval)."D"));
                            $dateEnd->add(new DateInterval("P".($interval)."D"));
                       }
                    } else {
                        if($reservation->getMasterReservationId() === null) { // Do not allow edits on sub-events
                            $reservation->setDescription($_REQUEST['description']);
                            $reservation->setTraining($training);
                            $reservation->setStart($dateStart->format('Y-m-d H:i:s'));
                            $reservation->setStop($dateEnd->format('Y-m-d H:i:s'));
                            if($staffNotes) {
                                $reservation->setStaffNotes($staffNotes);
                            }
                            $reservation->update();
                            // update reservations for other devices in room
                            $reservationsInRoom = Reservation::getSubEvents($db, $reservation->getReservationId());
                            foreach ($reservationsInRoom as $reservationInRoom) {
                                $reservation->load($reservationInRoom['id']);
                                $reservation->setDescription($_REQUEST['description']);
                                $reservation->setTraining($training);
                                $reservation->setStart($dateStart->format('Y-m-d H:i:s'));
                                $reservation->setStop($dateEnd->format('Y-m-d H:i:s'));
                                $reservation->update();
                            }
                        }
                    }

                    break;
				case 'check_conflicts':
					$dateStart = new DateTime($_REQUEST['start']);
                    $dateEnd = new DateTime($_REQUEST['end']);
					echo Reservation::checkEventConflicts($db,$_REQUEST['device_id'],$_REQUEST['res_user_id'],$dateStart->format('Y-m-d H:i:s'), $dateEnd->format('Y-m-d H:i:s'),isset($_REQUEST['id'])?$_REQUEST['id']:0);
					break;
            }
        }
    }
}


?>