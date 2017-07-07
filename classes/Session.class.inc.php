<?php

/**
 * Class Session
 * Used to keep create,delete and update user instrument sessions
 *
 */
class Session
{

	const DAY = 1, WEEK=2, YEAR=3, PERSON=4, GROUP=5, DEPARTMENT=6, DEVICE=7,MONTH=8,ALL_SESSIONS=0;

    private $sessionId;
	private $userId;
	private $start;
	private $stop;
	private $status;
	private $deviceId;
	private $elapsed;
	private $rateId;
	private $description;
	private $cfopId;
	private $rate;

	
	public function __construct(PDO $sqlDataBase)
	{
		$this->sqlDataBase = $sqlDataBase;
		$this->sessionId=0;
		$this->userId=0;
		$this->start="";
		$this->stop="";
		$this->status="";
		$this->deviceId=0;
		$this->cfopId="";
		$this->rateid=0;
		$this->description="";
	}
	
	public function __destruct()
	{
		
	}

	/**Session tracker keeps track of session on each device
	 * Used in session.php to track how long a person has been logged
	 * @param $deviceId
	 * @param $userId
	 */
	public function TrackSession($deviceId,$userId)
	{
		if($userId>0)
		{
			$queryOpenSession = "SELECT id FROM session WHERE user_id=:user_id AND device_id=:device_id AND (TIMESTAMPDIFF(MINUTE,stop,NOW()) < 15) ORDER BY id DESC";

			$openSession = $this->sqlDataBase->prepare($queryOpenSession);
            $openSession->execute(array(':device_id'=>$deviceId,':user_id'=>$userId));
            $openSessionArr = $openSession->fetch(PDO::FETCH_ASSOC);


			if($openSessionArr)
			{
// 				error_log("Open session detected updating".$userId,0);
				$queryUpdateSession = "UPDATE session SET stop=NOW(), elapsed=TIMESTAMPDIFF(MINUTE,start,NOW()) WHERE id =:id";
                $updateSession = $this->sqlDataBase->prepare($queryUpdateSession);
                $updateSession->execute(array(':id'=>$openSessionArr['id']));
			}
			else
			{
				error_log("Opening session for user ".$userId." on device ".$deviceId,0);
                $userCfop = new UserCfop($this->sqlDataBase);
                $defaultCfopId = $userCfop->LoadDefaultCfopl($userId);
				$queryStartSession = "INSERT INTO session (user_id,device_id,start,stop,rate,rate_type_id,min_use_time,cfop_id,rate_id)
				                        SELECT
				                          :user_id,
				                          :device_id,
				                          NOW(),
				                          NOW(), rate, rate_type_id, min_use_time,:default_cfop_id, rate_id FROM device_rate
				                          WHERE device_id=:device_id
				                          AND rate_id=(SELECT rate_id FROM users
				                          WHERE id=:user_id LIMIT 1)";
				 error_log($userId." ".$deviceId." ".$defaultCfopId,0);
				 $startSession = $this->sqlDataBase->prepare($queryStartSession);
                 $startSession->execute(array(':user_id'=>$userId,':device_id'=>$deviceId,':default_cfop_id'=>$defaultCfopId));
                 $sessionId = $this->sqlDataBase->lastInsertId();
			}

			$queryUpdateDeviceUser = "UPDATE device SET loggeduser=:loggeduser, lasttick=NOW() WHERE id=:id";
            $updateDeviceUser = $this->sqlDataBase->prepare($queryUpdateDeviceUser);
            $updateDeviceUser->execute(array(':loggeduser'=>$userId,':id'=>$deviceId));

		}	
		else
		{
			$queryUpdateDeviceNonUser = "UPDATE device SET loggeduser=0, lasttick=NOW() WHERE id=:id";
			$updateDeviceNonUser = $this->sqlDataBase->prepare($queryUpdateDeviceNonUser);
            $updateDeviceNonUser->execute(array(':id'=>$deviceId));
		}
	}

	/**Create a new session in the database
	 * @param $userId
	 * @param $start
	 * @param $stop
	 * @param $status
	 * @param $deviceId
	 * @param $description
	 * @param $cfop
	 */
	public function CreateSession($userId,$start,$stop,$status,$deviceId,$description,$cfop)
	{
		$this->userId=$userId;
		$this->start=$start;
		$this->stop=$stop;
		$this->status=$status;
		$this->deviceId=$deviceId;
		$this->description=$description;
		$this->cfopId=$cfop;
		
		$queryInsertSession="INSERT INTO session (user_id,start,stop,status,device_id,description,elapsed,cfop_id)
		                        VALUES(:user_id,:start,:stop,:status,device_id,:description,TIMESTAMPDIFF(MINUTE,:start,:stop),:cfop_id)";

		$insertSessionInfo = $this->sqlDataBase->prepare($queryInsertSession);
        $insertSessionInfo->execute(array(':user_id'=>$this->userId,':start'=>$this->start,':stop'=>$this->stop,':status'=>$this->status,':device_id'=>$this->deviceId,':description'=>$this->description,':cfop_id'=>$this->cfopId));

        $this->sessionId;
	}

	/**Load a session form the database into this object
	 * @param $id
	 */
	public function LoadSession($id)
	{
		$querySessionInfo = "SELECT * FROM session WHERE id=:session_id";
		$sessionInfo=$this->sqlDataBase->prepare($querySessionInfo);
        $sessionInfo->execute(array(':session_id'=>$id));
        $sessionInfoArr = $sessionInfo->fetch(PDO::FETCH_ASSOC);
		$this->sessionId = $sessionInfoArr["id"];
		$this->userId=$sessionInfoArr["user_id"];
		$this->start=$sessionInfoArr["start"];
		$this->stop=$sessionInfoArr["stop"];
		$this->status=$sessionInfoArr["status"];
		$this->deviceId=$sessionInfoArr["device_id"];
		$this->elapsed=$sessionInfoArr["elapsed"];
		$this->rate=$sessionInfoArr["rate"];
		$this->description=$sessionInfoArr["description"];
		$this->cfopId=$sessionInfoArr["cfop_id"];
	}

	/**
	 * Update the session with variables changed using the Setters
	 */
	public function UpdateSession()
	{
		$queryUpdateSession = "UPDATE session SET
		                        user_id=".$this->userId.",
		                        start=\"".$this->start."\",
		                        stop=\"".$this->stop."\",
		                        status=\"".$this->status."\",
		                        device_id=".$this->deviceId.",
		                        elapsed=".$this->elapsed.",
		                        description=\"".$this->description."\",
		                        cfop_id=\"".$this->cfopId."\",
		                        rate=".$this->rate."
		                       WHERE id=".$this->sessionId;
		$updateSession = $this->sqlDataBase->prepare($queryUpdateSession);
        $updateSession->execute(array(
                            ':user_id'=>$this->userId,
                            ':start'=>$this->start,
                            ':stop'=>$this->stop,
                            ':status'=>$this->status,
                            ':device_id'=>$this->deviceId,
                            ':elapsed'=>$this->elapsed,
                            ':description'=>$this->description,
                            ':cfop_id'=>$this->cfopId,
                            ':rate'=>$this->rate,
                            ':id'=>$this->sessionId));
	}

	/**
	 * Delete the currently loaded session
	 */
	public function Delete()
	{
		$queryDeleteSession = "DELETE FROM session WHERE id=:id";
		$deleteSession = $this->sqlDataBase->prepare($queryDeleteSession);
        $deleteSession->execute(array(':id'=>$this->sessionId));
	}

	/**
	 * Load the last session form the database into this object
	 */
    public function LoadLastSession()
    {
        $queryLastSession = "SELECT id FROM sessions ORDER BY start DESC LIMIT 1";
        $lastSessionId = $this->sqlDataBase->prepare($queryLastSession);
        $lastSessionId->execute();
        $lastSessionIdArr = $lastSessionId->fetch(PDO::FETCH_ASSOC);
        $this->LoadSession($lastSessionIdArr["id"]);
    }

	/**
	 * Load the first session from the database into this object
	 */
    public function LoadFirstSession()
    {
        $queryFirstSession = "SELECT id FROM sessions ORDER BY start ASC LIMIT 1";
        $firstSessionId = $this->sqlDataBase->prepare($queryFirstSession);
        $firstSessionId->execute();
        $firstSessionIdArr = $firstSessionId->fetch(PDO::FETCH_ASSOC);
        $this->LoadSession($firstSessionIdArr["id"]);
    }

	/** Get an array of sessions and their time usage
	 * can use an array to filter the sessions by users device and group
	 * the filter consists of an associative array with keys 'user', 'device' and 'group'
	 * consisting of IDs for each group
	 * Can group by different rows please see switch statement in function
	 * @param $startDateRange
	 * @param $endDateRange
	 * @param $groupBy
	 * @param $filtersArr
	 * @return array
	 */
    public function GetSessionsUsage($startDateRange, $endDateRange, $groupBy, $filtersArr)
    {
        $querySessions = "SELECT u.user_name, g.group_name, s.device_id, d.device_name, s.start, s.stop";

        if($groupBy)
        {
            $querySessions .= ", (SUM(s.elapsed/60)) as elapsed, (s.rate * SUM(s.elapsed)) as bill ";
        }
        else{
            $querySessions .=", (s.elapsed/60) as elapsed, (s.rate * s.elapsed) as bill ";
        }

        $querySessions .= "FROM session s, groups g, users u, device d
                            WHERE DATE(s.start) >= \"".$startDateRange."\"
                                AND DATE(s.stop)<=\"".$endDateRange."\"
                                AND s.user_id = u.id
                                AND s.device_id = d.id
                                AND u.group_id=g.id";

        //WHERE FILTERS
		//Filter based on users selected
        if(!empty($filtersArr['user']))
        {
            $querySessions .= " AND (";
            $count = 0;
            $userArr = $filtersArr['user'];
            foreach( $userArr as $userId)
            {
                if($count)
                {
                    $querySessions .= " OR s.user_id=".$userId;
                }
                else{
                    $querySessions .= " s.user_id=".$userId;
                }
                $count++;
            }
            $querySessions .= ")";
        }

		//Filters based on devices selected
        if(!empty($filtersArr['device']))
        {
            $querySessions .= " AND (";
            $count = 0;
            $deviceArr = $filtersArr['device'];
            foreach($deviceArr as $deviceId)
            {
                if($count)
                {
                    $querySessions .= " OR s.device_id=".$deviceId;
                }
                else{
                    $querySessions .= " s.device_id=".$deviceId;
                }
                $count++;

            }
            $querySessions .= ")";
        }

		//Filter based on the groups selected
        if(!empty($filtersArr['group']))
        {
            $querySessions .= " AND (";
            $count = 0;
            $groupArr = $filtersArr['group'];
            foreach( $groupArr as $groupId)
            {
                if($count)
                {
                    $querySessions .= " OR u.group_id=".$groupId;
                }
                else{
                    $querySessions .= " u.group_id=".$groupId;
                }
                $count++;
            }
            $querySessions .= ")";
        }

        //GROUP BY Filters
        switch ($groupBy)
        {
            case $this::DAY:
                //query by day
                $querySessions .= " GROUP BY DATE(s.start)";
                break;
            case $this::WEEK:
                //query by week
                $querySessions .= " GROUP BY YEAR(s.start), WEEK(s.start)";
                break;
            case $this::MONTH:
                //query by month
                $querySessions .= " GROUP BY YEAR(s.start), MONTH(s.start)";
                break;
            case $this::YEAR:
                //query by year
                $querySessions .= " GROUP BY YEAR(s.start)";
                break;
            case $this::PERSON:
                //query by user
                $querySessions .= " GROUP BY s.user_id";
                break;
            case $this::GROUP:
                //query by group
                $querySessions .= " GROUP BY u.group_id";
                break;
            case $this::DEVICE:
                //query by group
                $querySessions .= " GROUP BY s.device_id";
                break;
        }

        $sessionsUsage = $this->sqlDataBase->prepare($querySessions);
        $sessionsUsage->execute();
        $sessionUsageArr = $sessionsUsage->fetchAll(PDO::FETCH_ASSOC);

        return $sessionUsageArr;

    }

	public function GetSessionId()
	{
		return $this->sessionId;
	}
	
	public function GetUserID()
	{
		return $this->userId;
	}

	public function SetUserID($userId)
	{
		$this->userId=$userId;
	}

	public function GetStart()
	{
		return $this->start;
	}	

	public function SetStart($start)
	{
		$this->start = $start;
	}

	public function GetStop()
	{
		return $this->stop;
	}

	public function SetStop($stop)
	{
		$this->stop = $stop;
	}

	public function GetStatus()
	{
		return $this->status;
	}	

	public function SetStatus($status)
	{
		$this->status=$status;
	}

	public function GetDeviceID()
	{
		return $this->deviceId;
	}
	
	public function SetDeviceID($deviceId)
	{
		$this->deviceId=$deviceId;
	}

	public function GetElapsed()
	{
		return $this->elapsed;
	}

	public function SetElapsed($elapsed)
	{
		$this->elapsed=$elapsed;
	}

	public function GetRate()
	{

		return $this->rate;
	}
	
	public function SetRate($rate)
	{
		$this->rate=$rate;
	}

	public function GetDescription()
	{
		return $this->description;
	}

	public function SetDescription($description)	
	{
		$this->description=$description;
	}

	public function GetCfopId()
	{
		return $this->cfopId;
	}

	public function SetCfopId($cfopId)
	{
		$this->cfopId=$cfopId;
	}	
}
	
?>
