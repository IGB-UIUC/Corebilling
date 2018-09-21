<?php
set_time_limit(20);
include('includes/initializer.php');
if (isset($_POST['action']) && isset($_POST['user_id']) && isset($_POST['key'])) {
    //Load User information for user_id
    $user = new User ($db);
    $user->LoadUser($_POST['user_id']);

    //Verify the user is who is is saying he is by comparing the user key from the database to key given to the api
    if ($user->GetSecureKey() == $_POST ['key']) {

        //Create reservation object and load reservation info if we are given a reservation id
        $reservation = new Reservation ($db);
        if (isset($_POST['id'])) {
            $reservation->LoadReservation($_POST ['id']);
        }

        //For debugging purposes
        if ($user->isAdmin() // Admins can do anything
            || $user->GetUserId() == $reservation->getUserId() // Users can edit their own res
            || ($_POST['action'] == 'update_event_info' && $_POST['user_id']==$user->GetUserId() && $reservation->getReservationId()==0) // Users can create their own res
            || ($_POST['action'] == 'check_conflicts' && $_POST['res_user_id']==$user->GetUserId()) // Users can check conflicts
            || ($_POST['action']=='get_events' )) // Users can display events
        {
            switch ($_POST['action']) {
                case 'get_events':
                    echo $reservation->JsonEventsRange($_POST['start'], $_POST['end'], $_POST ['user_id'], $_POST ['device_id'],$_POST['training']==1);
                    break;
                case 'add_event':
                    $training = (isset($_POST['training']))?1:0;
                    $dateStart = new DateTime($_POST['start']);
                    $dateEnd = new DateTime($_POST['end']);
                    $reservation->CreateReservation($_POST['device_id'], $_POST ['user_id'], $dateStart->format('Y-m-d H:i:s'), $dateEnd->format('Y-m-d H:i:s'), $_POST['description'], $training);
//                     TODO implement repeat
                    break;
                case 'delete_event':
                    $reservation->DeleteReservation();
                    break;
                case 'update_event_time':
                	if($reservation->CheckEventTime(strtotime($_POST['start']), strtotime($_POST['end']), $_POST['id']) != 0){
	                    $reservation->setStart($_POST ['start']);
	                    $reservation->setStop($_POST ['end']);
	                    $reservation->UpdateReservation();
	                }
                    break;
                case 'finish_early':
                	if($reservation->IsInProgress()){
	                	$reservation->FinishEarly();
                	}
                	break;
                case 'update_event_info':
                    $training = (isset($_POST['training']))?$_POST['training']:0;
                    $repeat = (isset($_POST['repeat']))?(int)$_POST['repeat']:0;
                    $interval = (isset($_POST['interval']))?(int)$_POST['interval']:0;
                    $dateStart = new DateTime($_POST['start']);
                    $dateEnd = new DateTime($_POST['end']);
                    if ($reservation->getReservationId() == 0) {
                        for($i=0; $i<=$repeat; $i++) {
                            $reservation->CreateReservation($_POST['device_id'], $_POST ['user_id'], $dateStart->format('Y-m-d H:i:s'), $dateEnd->format('Y-m-d H:i:s'), $_POST['description'], $training);
                            $dateStart->add(new DateInterval("P".($interval)."D"));
                            $dateEnd->add(new DateInterval("P".($interval)."D"));
                       }
                    }

                    else {
                        for($i=1; $i<=$repeat; $i++) {
                            $dateStart->add(new DateInterval("P".($interval)."D"));
                            $dateEnd->add(new DateInterval("P".($interval)."D"));
                            $reservation->CreateReservation($_POST['device_id'], $_POST ['user_id'], $dateStart->format('Y-m-d H:i:s'), $dateEnd->format('Y-m-d H:i:s'), $_POST['description'], $training);
                        }

                        $reservation->setDescription($_POST['description']);
                        $reservation->setTraining($training);
                        $reservation->setStart($dateStart->format('Y-m-d H:i:s'));
                        $reservation->setStop($dateEnd->format('Y-m-d H:i:s'));
                        $reservation->UpdateReservation();
                    }

                    break;
				case 'check_conflicts':
					$dateStart = new DateTime($_POST['start']);
                    $dateEnd = new DateTime($_POST['end']);
					echo $reservation->CheckEventConflicts($_POST['device_id'],$_POST['res_user_id'],$dateStart->format('Y-m-d H:i:s'), $dateEnd->format('Y-m-d H:i:s'),isset($_POST['id'])?$_POST['id']:0);
					break;
            }
        }
    }
}


?>