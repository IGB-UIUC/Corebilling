<?php

class Reservation
{
    const ALL = 1, NON_TRAINING = 2, TRAINING = 3;
    private $db;
    private $reservationId;
    private $deviceId;
    private $userId;
    private $start;
    private $stop;
    private $description;
    private $training;
    private $dateCreated;
    private $deleted;
    private $finishedEarly;
    private $masterReservationId;

    public function __construct(PDO $db)
    {
        $this->db = $db;
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
     * @param null $masterReservationId
     * @return int
     */
    public function create($deviceId, $userId, $start, $stop, $description, $training, $masterReservationId = null)
    {
        $this->deviceId = $deviceId;
        $this->userId = $userId;
        $this->start = $start;
        $this->stop = $stop;
        $this->description = $description;
        $this->training = $training;
        $this->masterReservationId = $masterReservationId;


        if (self::checkEventConflicts($this->db, $this->deviceId, $this->userId, $this->start, $this->stop) == 1) {
            $queryCreateReservation = "INSERT INTO reservation_info (device_id,user_id,start,stop,description,training,date_created,master_reservation_id)
										VALUES(:device_id,:user_id,:start,:stop,:description,:training,NOW(),:master)";
            $createReservation = $this->db->prepare($queryCreateReservation);
            $createReservation->execute(array(':device_id' => $this->deviceId, ':user_id' => $this->userId, ':start' => $this->start, ':stop' => $this->stop, ':description' => $this->description, ':training' => $this->training, ':master' => $this->masterReservationId));
            $this->reservationId = $this->db->lastInsertId();
            return 1;
        } else {
            return 0;
        }
    }


    /**
     * Load reservation information
     * @param int $reservationId
     */
    public function load($reservationId)
    {
        $queryLoadReservationInfo = "SELECT * FROM reservation_info WHERE id=:reservation_id";
        $reservationInfo = $this->db->prepare($queryLoadReservationInfo);
        $reservationInfo->execute(array(':reservation_id' => $reservationId));
        $reservationInfoArr = $reservationInfo->fetch(PDO::FETCH_ASSOC);
        if ($reservationInfoArr) {
            $this->reservationId = $reservationId;
            $this->deviceId = $reservationInfoArr['device_id'];
            $this->userId = $reservationInfoArr['user_id'];
            $this->start = $reservationInfoArr['start'];
            $this->stop = $reservationInfoArr['stop'];
            $this->description = $reservationInfoArr['description'];
            $this->training = $reservationInfoArr['training'];
            $this->dateCreated = $reservationInfoArr['date_created'];
            $this->deleted = $reservationInfoArr['deleted'];
            $this->finishedEarly = $reservationInfoArr['finished_early'];
            $this->masterReservationId = $reservationInfoArr['master_reservation_id'];
        }
    }


    /**
     * Delete the reservation currently loaded
     */
    public function delete()
    {
        $queryDeleteReservation = "UPDATE reservation_info SET deleted = 1 WHERE id=:reservation_id";
        $deleteReservationInfo = $this->db->prepare($queryDeleteReservation);
        $deleteReservationInfo->execute(array(':reservation_id' => $this->reservationId));
    }


    /**Update the reservation with the setters changes
     * @return int
     */
    public function update()
    {
        //No update feature needed yet
        if (self::checkEventConflicts($this->db, $this->deviceId, $this->userId, $this->start, $this->stop, $this->reservationId) == 1) {
            $queryUpdateReservation = "UPDATE reservation_info SET start=:start, stop=:stop, description=:description, training=:training WHERE id=:reservation_id";
            $updateReservation = $this->db->prepare($queryUpdateReservation);
            $updateReservation->execute(array(':start' => $this->start, ':stop' => $this->stop, ':reservation_id' => $this->reservationId, ':description' => $this->description, ':training' => $this->training));

            return 1;
        } else {
            return 0;
        }
    }

    public function finishEarly()
    {
        $sql = "UPDATE reservation_info SET finished_early=NOW() where id=:reservation_id";
        $updstmt = $this->db->prepare($sql);
        $updstmt->execute(array(':reservation_id' => $this->reservationId));
        return 1;
    }


    /** Check for event conflicts prior to trying to enter a reservation into the database
     * or updatign a reservation with a new time range
     * @param $deviceId
     * @param $startTimeUnix
     * @param $stopTimeUnix
     * @param int $reservationId
     * @return int
     */
    public static function checkEventConflicts($db, $deviceId, $userId, $startTimeUnix, $stopTimeUnix, $reservationId = 0)
    {
        $queryConflicts = "SELECT COUNT(*) AS num_conflicts FROM reservation_info
				WHERE device_id=:device_id
				AND deleted = 0
			    AND (
						(UNIX_TIMESTAMP(start) < UNIX_TIMESTAMP(:start_time_unix) AND UNIX_TIMESTAMP(stop) > UNIX_TIMESTAMP(:start_time_unix))
			     	OR
						(UNIX_TIMESTAMP(stop) > UNIX_TIMESTAMP(:stop_time_unix) AND UNIX_TIMESTAMP(start) < UNIX_TIMESTAMP(:stop_time_unix ))
					OR
						(UNIX_TIMESTAMP(start) >= UNIX_TIMESTAMP(:start_time_unix) AND UNIX_TIMESTAMP(stop) <= UNIX_TIMESTAMP(:stop_time_unix ))
					) AND ID!=:reservation_id";
        $deviceconflicts = $db->prepare($queryConflicts);
        $deviceconflicts->execute(array(':device_id' => $deviceId, ':start_time_unix' => $startTimeUnix, ':stop_time_unix' => $stopTimeUnix, ':reservation_id' => $reservationId));
        $conflictsArr = $deviceconflicts->fetch(PDO::FETCH_ASSOC);

        if ($conflictsArr["num_conflicts"] == 0 && $startTimeUnix < $stopTimeUnix && $deviceId > 0) {
            return 1;
        } else {
            // Device conflict
            return 0;
        }
    }

    public static function checkEventTime($db, $startTimeUnix, $stopTimeUnix, $reservationId = 0)
    {
        if ($startTimeUnix > $stopTimeUnix || $startTimeUnix - 2 * 60 * 60 < time()) {
            // Can't move an event into the past
            return 0;
        } else {
            if ($reservationId == 0) {
                // New event
                return 1;
            } else {
                // Existing event
                $queryTime = "SELECT UNIX_TIMESTAMP(start) as start, master_reservation_id from reservation_info where id=:reservation_id";
                $timestmt = $db->prepare($queryTime);
                $timestmt->execute(array(':reservation_id' => $reservationId));
                $timeArr = $timestmt->fetch(PDO::FETCH_ASSOC);
                if ($timeArr['start'] - 2 * 60 * 60 < time()) {
                    // Can't move an event out of the past
                    return 0;
                }
                if($timeArr['master_reservation_id'] != null){
                    // Can't move a sub-event; move the master instead
                    return 0;
                }
                return 1;
            }
        }
    }

    public function isInProgress()
    {
        $sdt = new DateTime($this->start);
        $sts = intval($sdt->format("U"));
        $edt = new DateTime($this->stop);
        $ets = intval($edt->format("U"));
        $now = time();
        return ($now > $sts && $now < $ets);
    }

    /**
     * @param PDO $db
     * @param $id
     * @return array
     */
    public static function getSubEvents($db, $id)
    {
        $query = 'select id from reservation_info where master_reservation_id=:id';
        $stmt = $db->prepare($query);
        $stmt->execute(array(':id'=>$id));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Return json string representing events
     * @param $start
     * @param $end
     * @param $userId
     * @param $deviceId
     * @return string
     */
    public static function getEventsInRangeJSON($db, $start, $end, $userId, $deviceId, $training)
    { // TODO this might be too specific for this class, and should be in calendar_api
        $eventsArr = array();
        $events = self::getEventsInRange($db, $start, $end, $userId, $deviceId, $training);

        foreach ($events as $id => $event) {
            $missed = self::getMissed($db, $event['id']);

            $color = CAL_DEFAULT_COLOR;
            if ($event['master_device']) {
                $color = CAL_ROOM_COLOR;
            } elseif ($event['training']) {
                $color = CAL_TRAINING_COLOR;
            } elseif ($missed) {
                $color = CAL_MISSED_COLOR;
            }

            $borderColor = CAL_DEFAULT_COLOR;
            if ($event['master_device']) {
                $borderColor = CAL_ROOM_COLOR;
            } elseif ($missed) {
                $borderColor = CAL_MISSED_COLOR;
            }

            $title = $event['full_device_name'] . " - " . $event['user_name'];
            if ($event['master_device']) {
                $title = $event['master_device'] . " - " . $event['user_name'];
            }

            $buildJson = array(
                'id' => $event['id'],
                'title' => $title,
                'start' => $event['starttime'],
                'end' => $event['stoptime'],
                'allDay' => false,
                'username' => $event['user_name'],
                'userid' => $event['user_id'],
                'description' => $event['description'],
                'device_name' => $event['full_device_name'],
                'training' => $event['training'],
                'color' => $color,
                'missed' => $missed,
                'borderColor' => $borderColor,
                'finishedEarly' => $event['finished_early'],
                'masterDevice' => $event['master_device']
            );
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
    public static function getEventsInRange($db, $start, $end, $userId, $deviceId, $training)
    {
        $queryEvents = "SELECT e.id, d.device_name, d.full_device_name, e.device_id, u.user_name, u.first, u.last, u.email, e.user_id, e.description, e.start AS starttime, e.stop AS stoptime, e.training, e.finished_early, GROUP_CONCAT(g.group_name separator ', ') as group_name, md.full_device_name as master_device
                            FROM reservation_info e INNER JOIN device d ON d.id=e.device_id INNER JOIN users u ON u.id=e.user_id left join user_groups ug on u.id=ug.user_id LEFT JOIN `groups` g ON g.id=ug.group_id left join reservation_info m on e.master_reservation_id=m.id left join device md on m.device_id=md.id";
        if ($training) {
            $trainingTest = " and e.training=1";
        } else {
            $trainingTest = "";
        }
        if ($deviceId == 0) { // My reservations
            $queryEvents .= " WHERE
                            UNIX_TIMESTAMP(e.start)>=UNIX_TIMESTAMP(:start)
                            AND UNIX_TIMESTAMP(e.stop)<= UNIX_TIMESTAMP(:stop)
                            AND e.deleted=0
                            AND u.id=:user_id" . $trainingTest . "
                            AND e.master_reservation_id is null
                            GROUP BY e.id
                            ORDER BY e.device_id, e.start";
            $queryParameters[':user_id'] = $userId;
        } else if ($deviceId == -1) { // Missed Reservations
            $queryEvents .= " WHERE
	     					e.id not in (select r.id from reservation_info r inner join `session` s on s.start <= r.stop and s.stop >= r.start where UNIX_TIMESTAMP(r.start)>=UNIX_TIMESTAMP(:start) and UNIX_TIMESTAMP(r.start)<=UNIX_TIMESTAMP(:stop) and r.device_id=s.device_id and r.user_id=s.user_id)
	     					and UNIX_TIMESTAMP(e.start)>=UNIX_TIMESTAMP(:start)
	     					and UNIX_TIMESTAMP(e.start)<=UNIX_TIMESTAMP(:stop)
	     					AND e.deleted=:deleted
	     					and e.stop<NOW()" . $trainingTest . "
	     					and d.status_id!=3
	     					AND e.master_reservation_id is null
	     					GROUP BY e.id
	     					order by e.start";
        } else if ($deviceId == -2) { // All devices
            $queryEvents .= " WHERE
	     					UNIX_TIMESTAMP(e.start)>=UNIX_TIMESTAMP(:start)
	     					and UNIX_TIMESTAMP(e.start)<=UNIX_TIMESTAMP(:stop)" . $trainingTest . "
	     					AND e.deleted=0
	     					AND e.master_reservation_id is null
	     					GROUP BY e.id
	     					order by e.start";
        } else if ($deviceId == -3) { // Deleted reservations
            $queryEvents .= " WHERE
							UNIX_TIMESTAMP(e.start)>=UNIX_TIMESTAMP(:start)
							AND UNIX_TIMESTAMP(e.start)<=UNIX_TIMESTAMP(:stop)" . $trainingTest . "
							AND e.deleted=1
							GROUP BY e.id
							ORDER BY e.start";
        } else {
            $queryEvents .= " WHERE
                            UNIX_TIMESTAMP(e.start)>=UNIX_TIMESTAMP(:start)
                            AND UNIX_TIMESTAMP(e.start)<= UNIX_TIMESTAMP(:stop)
                            AND e.deleted=0
                            AND e.device_id=:device_id" . $trainingTest . "
                            GROUP BY e.id
                            ORDER BY e.start";
            $queryParameters[':device_id'] = $deviceId;
        }

        $queryParameters[':start'] = $start;
        $queryParameters[':stop'] = $end;

        $events = $db->prepare($queryEvents);
        $events->execute($queryParameters);
        $eventsArr = $events->fetchAll(PDO::FETCH_ASSOC);

        return $eventsArr;
    }

    public static function getEventsInRangeForSpreadsheet($db, $start, $end, $userId, $deviceId, $training)
    {
        $queryEvents = "SELECT d.full_device_name as Device, u.user_name as Username, concat(u.first,concat(' ',u.last)) as Name, u.email as Email, e.description as Description, e.start as 'Start Time', e.stop as 'Stop Time', e.description as 'Description', e.training as Training, c.cfop as CFOP FROM reservation_info e INNER JOIN device d ON d.id=e.device_id INNER JOIN users u ON u.id=e.user_id left join user_cfop c ON c.created = (select max(c1.created) from user_cfop c1 where c1.user_id=e.user_id and c1.created < e.start) and c.user_id=e.user_id";

        if ($training) {
            $trainingTest = " and e.training=1";
        } else {
            $trainingTest = "";
        }
        if ($deviceId == 0) { // My reservations
            $queryEvents .= " WHERE
                            UNIX_TIMESTAMP(e.start)>=UNIX_TIMESTAMP(:start)
                            AND UNIX_TIMESTAMP(e.stop)<= UNIX_TIMESTAMP(:stop)
                            AND e.deleted=0
                            AND u.id=:user_id" . $trainingTest . "
                            ORDER BY e.device_id, e.start";
            $queryParameters[':user_id'] = $userId;
        } else if ($deviceId == -1) { // Missed Reservations
            $queryEvents .= " WHERE
	     					e.id not in (select r.id from reservation_info r inner join `session` s on s.start <= r.stop and s.stop >= r.start where UNIX_TIMESTAMP(r.start)>=UNIX_TIMESTAMP(:start) and UNIX_TIMESTAMP(r.start)<=UNIX_TIMESTAMP(:stop) and r.device_id=s.device_id and r.user_id=s.user_id)
	     					and UNIX_TIMESTAMP(e.start)>=UNIX_TIMESTAMP(:start)
	     					and UNIX_TIMESTAMP(e.start)<=UNIX_TIMESTAMP(:stop)
	     					AND e.deleted=0
	     					and e.stop<NOW()" . $trainingTest . "
	     					and d.status_id!=3
	     					order by e.start";
        } else if ($deviceId == -2) { // All devices
            $queryEvents .= " WHERE
	     					UNIX_TIMESTAMP(e.start)>=UNIX_TIMESTAMP(:start)
	     					and UNIX_TIMESTAMP(e.start)<=UNIX_TIMESTAMP(:stop)" . $trainingTest . "
	     					AND e.deleted=0
	     					order by e.start";
        } else if ($deviceId == -3) { // Deleted reservations
            $queryEvents .= " WHERE
							UNIX_TIMESTAMP(e.start)>=UNIX_TIMESTAMP(:start)
							AND UNIX_TIMESTAMP(e.start)<=UNIX_TIMESTAMP(:stop)" . $trainingTest . "
							AND e.deleted=1
							ORDER BY e.start";
        } else {
            $queryEvents .= " WHERE
                            UNIX_TIMESTAMP(e.start)>=UNIX_TIMESTAMP(:start)
                            AND UNIX_TIMESTAMP(e.start)<= UNIX_TIMESTAMP(:stop)
                            AND e.deleted=0
                            AND e.device_id=:device_id" . $trainingTest . "
                            ORDER BY e.start";
            $queryParameters[':device_id'] = $deviceId;
        }

        $queryParameters[':start'] = $start;
        $queryParameters[':stop'] = $end;

        $events = $db->prepare($queryEvents);
        $events->execute($queryParameters);
        $eventsArr = $events->fetchAll(PDO::FETCH_ASSOC);

        for ($i = 0; $i < count($eventsArr); $i++) {
            $eventsArr[$i]['CFOP'] = UserCfop::formatCfop($eventsArr[$i]['CFOP']);
        }

        return $eventsArr;
    }


    public static function getMissed($db, $id)
    {
        $sql = "select (case UNIX_TIMESTAMP(r.stop)<UNIX_TIMESTAMP(NOW()) and d.status_id!=3 when true then count(r.id) when false then 1 end) as count from reservation_info r inner join `session` s on s.start<=r.stop and s.stop>=r.start inner join device d on d.id=r.device_id where r.device_id=s.device_id and r.user_id=s.user_id and r.id=:id";
        $args = array(":id" => $id);
        $missed = $db->prepare($sql);
        $missed->execute($args);
        $missed = $missed->fetch(PDO::FETCH_ASSOC);
        return $missed['count'] == 0;
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

    /**
     * @return int
     */
    public function getMasterReservationId()
    {
        return $this->masterReservationId;
    }

}


?>