<?php

/**
 * Class used to manage a device reservation object
 * can Update delete and create a reservation on the calendar
 * also used to verify there are no reservation conflicts using the reservation type sub classes
 * Enter description here ...
 * @author nevoband
 *
 */
class Reservation
{
    const ALL=1,NON_TRAINING=2,TRAINING=3;
    private $sqlDatabase;
    private $reservationId;
    private $deviceId;
    private $userId;
    private $start;
    private $stop;
    private $description;
    private $training;
    private $dateCreated;

    public function __construct(PDO $sqlDatabase)
    {
        $this->sqlDatabase = $sqlDatabase;
        $this->reservationId = 0;
        $this->userId = 0;
        $this->deviceId = 0;
    }

    public function __destruct()
    {

    }

    /**Create a new reservation on the calendar
     * @param $deviceId
     * @param $userId
     * @param $start
     * @param $stop
     * @param $description
     * @param $training
     * @return int
     */
    public function CreateReservation($deviceId, $userId, $start, $stop, $description, $training)
    {
        $this->deviceId = $deviceId;
        $this->userId = $userId;
        $this->start = $start;
        $this->stop = $stop;
        $this->description = $description;
        $this->training = $training;


        if ($this->SaveReservation()) {
            return 1;
        } else {
            return 0;
        }
    }

    /**Save the reservation object to the database
     * @return int
     */
    public function SaveReservation()
    {
        error_log('start save reservation',0);
        if ($this->CheckEventConflicts($this->deviceId, $this->start, $this->stop)) {
            error_log('passed conflict check',0);
            $queryCreateReservation = "INSERT INTO reservation_info (device_id,user_id,start,stop,description,training,date_created)
										VALUES(:device_id,:user_id,:start,:stop,:description,:training,NOW())";
            $createReservation = $this->sqlDatabase->prepare($queryCreateReservation);
            $createReservation->execute(array(':device_id'=>$this->deviceId,':user_id'=>$this->userId,':start'=>$this->start,':stop'=>$this->stop, ':description'=>$this->description, ':training'=>$this->training));
            $this->reservationId = $this->sqlDatabase->lastInsertId();
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Load reservation information
     * @param unknown_type $reservationId
     */
    public function LoadReservation($reservationId)
    {
        $queryLoadReservationInfo = "SELECT * FROM reservation_info WHERE id=:reservation_id";
        $reservationInfo = $this->sqlDatabase->prepare($queryLoadReservationInfo);
        $reservationInfo->execute(array(':reservation_id'=>$reservationId));
        $reservationInfoArr = $reservationInfo->fetch(PDO::FETCH_ASSOC);
        if($reservationInfoArr) {
            $this->reservationId = $reservationId;
            $this->deviceId = $reservationInfoArr['device_id'];
            $this->userId = $reservationInfoArr['user_id'];
            $this->start = $reservationInfoArr['start'];
            $this->stop = $reservationInfoArr['stop'];
            $this->description = $reservationInfoArr['description'];
            $this->training = $reservationInfoArr['training'];
            $this->dateCreated = $reservationInfoArr['date_created'];
        }
    }

    /**
     * Delete the reservation currently loaded
     */
    public function DeleteReservation()
    {
        $queryDeleteReservation = "DELETE FROM reservation_info WHERE id=:reservation_id";
        $deleteReservationInfo= $this->sqlDatabase->prepare($queryDeleteReservation);
        $deleteReservationInfo->execute(array(':reservation_id'=>$this->reservationId));
    }

    /**Update the reservation with the setters changes
     * @return int
     */
    public function UpdateReservation()
    {
        //No update feature needed yet
        if ($this->CheckEventConflicts($this->deviceId, $this->start, $this->stop, $this->reservationId))
        {
            $queryUpdateReservation = "UPDATE reservation_info SET start=:start, stop=:stop, description=:description WHERE id=:reservation_id" ;
            $updateReservation = $this->sqlDatabase->prepare($queryUpdateReservation);
            $updateReservation->execute(array(':start'=>$this->start,':stop'=>$this->stop,':reservation_id'=>$this->reservationId,':description'=>$this->description));

            return 1;
        } else {
            return 0;
        }
    }

    /** Check for event conflicts prior to trying to enter a reservation into the database
     * or updatign a reservation with a new time range
     * @param $deviceId
     * @param $startTimeUnix
     * @param $stopTimeUnix
     * @param int $reservationId
     * @return int
     */
    public function CheckEventConflicts($deviceId, $startTimeUnix, $stopTimeUnix, $reservationId = 0)
    {
        $queryConflicts = "SELECT COUNT(*) AS num_conflicts FROM reservation_info
				WHERE device_id=:device_id
			    AND (
						(UNIX_TIMESTAMP(start) < UNIX_TIMESTAMP(:start_time_unix) AND UNIX_TIMESTAMP(stop) > UNIX_TIMESTAMP(:start_time_unix))
			     	OR
						(UNIX_TIMESTAMP(stop) > UNIX_TIMESTAMP(:stop_time_unix) AND UNIX_TIMESTAMP(start) < UNIX_TIMESTAMP(:stop_time_unix ))
					OR
						(UNIX_TIMESTAMP(start) >= UNIX_TIMESTAMP(:start_time_unix) AND UNIX_TIMESTAMP(stop) <= UNIX_TIMESTAMP(:stop_time_unix ))
					) AND ID!=:reservation_id";
        $conflicts = $this->sqlDatabase->prepare($queryConflicts);
        $conflicts->execute(array(':device_id'=>$deviceId,':start_time_unix'=>$startTimeUnix,':stop_time_unix'=>$stopTimeUnix,':reservation_id'=>$reservationId));
        $conflictsArr = $conflicts->fetch(PDO::FETCH_ASSOC);


        if ($conflictsArr["num_conflicts"] == 0 && $startTimeUnix < $stopTimeUnix && $this->deviceId >0) {
            error_log('passed conflicts test',1);
            return 1;
        } else {
            error_log('failed conflicts test',1);
            return 0;
        }
    }

    /** Return json string representing events
     * @param $start
     * @param $end
     * @param $userId
     * @param $deviceId
     * @return string
     */
    public function JsonEventsRange($start,$end,$userId,$deviceId)
    {
        $eventsArr = array();
        $events = $this->EventsRange($start,$end,$userId,$deviceId);

        foreach($events as $id=>$event)
        {

            $buildJson = array('id'=>$event['id'],'title'=>$event['user_name'], 'start'=>$event['starttime'], 'end'=>$event['stoptime'], 'allDay'=>false, 'description'=>$event['description'],'device_name'=>$event['full_device_name'],'training'=>$event['training']);
            array_push($eventsArr, $buildJson);
        }

        return json_encode($eventsArr);
    }

    /** Get a range of events for the calendar for a certain user idor
     * @param $start
     * @param $end
     * @param $userId
     * @param $deviceId
     * @return array
     */
    private function EventsRange($start, $end,$userId,$deviceId)
    {
        $queryEvents = "SELECT e.id, d.device_name, d.full_device_name, e.device_id, u.user_name, u.first, u.last, u.email, e.user_id, e.description, e.start AS starttime, e.stop AS stoptime, e.training
                            FROM reservation_info e, device d, users u";

        if($deviceId==0)
        {
            $queryEvents.=" WHERE
                            UNIX_TIMESTAMP(e.start)>=UNIX_TIMESTAMP(:start)
                            AND UNIX_TIMESTAMP(e.stop)<= UNIX_TIMESTAMP(:stop)
                            AND u.id=e.user_id
                            AND u.id=:user_id
                            AND d.id=e.device_id
                            ORDER BY e.device_id, e.start";
            $queryParameters[':user_id'] =$userId;
        }
        else
        {
            $queryEvents.=" WHERE
                            UNIX_TIMESTAMP(e.start)>=UNIX_TIMESTAMP(:start)
                            AND UNIX_TIMESTAMP(e.start)<= UNIX_TIMESTAMP(:stop)
                            AND u.id=e.user_id
                            AND d.id=e.device_id
                            AND e.device_id=:device_id
                            ORDER BY e.start";
            $queryParameters[':device_id']=$deviceId;
        }

        $queryParameters[':start']=$start;
        $queryParameters[':stop']=$end;

        $events = $this->sqlDatabase->prepare($queryEvents);
        $events->execute($queryParameters);
        $eventsArr = $events->fetchAll(PDO::FETCH_ASSOC);

        return $eventsArr;
    }

    private function UnixTimeToTimeStamp($dateUnix)
    {
        $timeStamp = date('Y-m-d H:i:s', $dateUnix);
        return $timeStamp;
    }

    //Getters Setters
    public function getReservationId()
    {
        return $this->reservationId;
    }

    public function getDeviceId()
    {
        return $this->deviceId;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getStop()
    {
        return $this->stop;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getTraining()
    {
        return $this->training;
    }

    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    public function getReservationTypeId()
    {
        return $this->reservationTypeId;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getDisplay()
    {
        return $this->display;
    }

    public function setDeviceId($x)
    {
        $this->deviceId = $x;
    }

    public function setUserId($x)
    {
        $this->userId = $x;
    }

    public function setStart($x)
    {
        $x = $this->start = $x;
    }

    public function setStop($x)
    {
        $x = $this->stop = $x;
    }

    public function setDescription($x)
    {
        $this->description = $x;
    }

    public function setTraining($x)
    {
        $this->training = $x;
    }

    public function setDateCreated($x)
    {
        $this->dateCreated = $x;
    }

    public function setReservationTypeId($x)
    {
        $this->reservationTypeId = $x;
    }

    public function setValue($x)
    {
        $this->value = $x;
    }
}

?>