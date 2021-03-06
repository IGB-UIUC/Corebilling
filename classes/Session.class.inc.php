<?php

/**
 * Class Session
 * Used to keep create,delete and update user instrument sessions
 *
 */
class Session
{
    private $db;

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


    public function __construct(PDO $db) {
        $this->db = $db;
        $this->sessionId = 0;
        $this->userId = 0;
        $this->start = "";
        $this->stop = "";
        $this->status = "";
        $this->deviceId = 0;
        $this->cfopId = "";
        $this->rateid = 0;
        $this->description = "";
    }

    public function __destruct() {

    }

    /**Session tracker keeps track of session on each device
     * Used in session.php to track how long a person has been logged
     * @param $deviceId
     * @param $userId
     */
    public static function trackSession($db, $deviceId, $userId) {
        if ( $userId > 0 ) {
            $queryOpenSession = "select id from session where user_id=:user_id and device_id=:device_id and (TIMESTAMPDIFF(minute,stop,NOW()) < 15) order by id desc";

            $openSession = $db->prepare($queryOpenSession);
            $openSession->execute(array(':device_id' => $deviceId, ':user_id' => $userId));
            $openSessionArr = $openSession->fetch(PDO::FETCH_ASSOC);


            if ( $openSessionArr ) {
// 				error_log("Open session detected updating".$userId,0);
                $queryUpdateSession = "update session set stop=NOW(), elapsed=TIMESTAMPDIFF(minute,start,NOW()) where id =:id";
                $updateSession = $db->prepare($queryUpdateSession);
                $updateSession->execute(array(':id' => $openSessionArr['id']));
            } else {
                error_log("Opening session for user " . $userId . " on device " . $deviceId, 0);
                $userCfop = new UserCfop($db);
                $defaultCfopId = $userCfop->loadDefaultCfop($userId);
                $queryStartSession = "insert into session (user_id,device_id,start,stop,rate,rate_type_id,min_use_time,cfop_id,rate_id)
				                        select
				                          :user_id,
				                          :device_id,
				                          NOW(),
				                          NOW(), rate, rate_type_id, min_use_time,:default_cfop_id, rate_id from device_rate
				                          where device_id=:device_id
				                          and rate_id=(select rate_id from users
				                          where id=:user_id limit 1)";
                error_log($userId . " " . $deviceId . " " . $defaultCfopId, 0);
                $startSession = $db->prepare($queryStartSession);
                $startSession->execute(
                    array(':user_id' => $userId, ':device_id' => $deviceId, ':default_cfop_id' => $defaultCfopId));
                $sessionId = $db->lastInsertId();
            }

            $queryUpdateDeviceUser = "update device set loggeduser=:loggeduser, lasttick=NOW() where id=:id";
            $updateDeviceUser = $db->prepare($queryUpdateDeviceUser);
            $updateDeviceUser->execute(array(':loggeduser' => $userId, ':id' => $deviceId));

        } else {
            $queryUpdateDeviceNonUser = "update device set loggeduser=0, lasttick=NOW() where id=:id";
            $updateDeviceNonUser = $db->prepare($queryUpdateDeviceNonUser);
            $updateDeviceNonUser->execute(array(':id' => $deviceId));
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
    public function create($userId, $start, $stop, $status, $deviceId, $description, $cfop) {
        $this->userId = $userId;
        $this->start = $start;
        $this->stop = $stop;
        $this->status = $status;
        $this->deviceId = $deviceId;
        $this->description = $description;
        $this->cfopId = $cfop;

        $queryInsertSession = "insert into session (user_id,start,stop,status,device_id,description,elapsed,cfop_id)
		                        values(:user_id,:start,:stop,:status,device_id,:description,TIMESTAMPDIFF(minute,:start,:stop),:cfop_id)";

        $insertSessionInfo = $this->db->prepare($queryInsertSession);
        $insertSessionInfo->execute(
            array(
                ':user_id' => $this->userId,
                ':start' => $this->start,
                ':stop' => $this->stop,
                ':status' => $this->status,
                ':device_id' => $this->deviceId,
                ':description' => $this->description,
                ':cfop_id' => $this->cfopId,
            ));

        $this->sessionId;
    }

    /**Load a session form the database into this object
     * @param $id
     */
    public function load($id) {
        $querySessionInfo = "select * from session where id=:session_id";
        $sessionInfo = $this->db->prepare($querySessionInfo);
        $sessionInfo->execute(array(':session_id' => $id));
        $sessionInfoArr = $sessionInfo->fetch(PDO::FETCH_ASSOC);
        $this->sessionId = $sessionInfoArr["id"];
        $this->userId = $sessionInfoArr["user_id"];
        $this->start = $sessionInfoArr["start"];
        $this->stop = $sessionInfoArr["stop"];
        $this->status = $sessionInfoArr["status"];
        $this->deviceId = $sessionInfoArr["device_id"];
        $this->elapsed = $sessionInfoArr["elapsed"];
        $this->rate = $sessionInfoArr["rate"];
        $this->description = $sessionInfoArr["description"];
        $this->cfopId = $sessionInfoArr["cfop_id"];
    }

    /**
     * Update the session with variables changed using the Setters
     */
    public function update() {
        $queryUpdateSession = "UPDATE session SET
		                        user_id=" . $this->userId . ",
		                        start=\"" . $this->start . "\",
		                        stop=\"" . $this->stop . "\",
		                        status=\"" . $this->status . "\",
		                        device_id=" . $this->deviceId . ",
		                        elapsed=" . $this->elapsed . ",
		                        description=\"" . $this->description . "\",
		                        cfop_id=\"" . $this->cfopId . "\",
		                        rate=" . $this->rate . "
		                       WHERE id=" . $this->sessionId;
        $updateSession = $this->db->prepare($queryUpdateSession);
        $updateSession->execute(
            array(
                ':user_id' => $this->userId,
                ':start' => $this->start,
                ':stop' => $this->stop,
                ':status' => $this->status,
                ':device_id' => $this->deviceId,
                ':elapsed' => $this->elapsed,
                ':description' => $this->description,
                ':cfop_id' => $this->cfopId,
                ':rate' => $this->rate,
                ':id' => $this->sessionId,
            ));
    }

	/**
	 * @param PDO $db
	 * @param $date
	 * @param $device
	 * @return mixed
	 */
	public static function getSessions($db, $date, $device) {
        $query = "select u.user_name, g.group_name, s.device_id, d.device_name, s.start, s.stop 
		from session s inner join users u on u.id=s.user_id inner join device d on d.id=s.device_id left join user_groups ug on u.id=ug.user_id left join `groups` g on g.id=ug.group_id 
		where d.id=:device and (DATE(s.start)=:date or DATE(s.stop)=:date)";
        $stmt = $db->prepare($query);
        $stmt->execute(array(":date" => $date, ":device" => $device));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getId() {
        return $this->sessionId;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function getStart() {
        return $this->start;
    }

    public function setStart($start) {
        $this->start = $start;
    }

    public function getStop() {
        return $this->stop;
    }

    public function setStop($stop) {
        $this->stop = $stop;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getDeviceId() {
        return $this->deviceId;
    }

    public function setDeviceId($deviceId) {
        $this->deviceId = $deviceId;
    }

    public function getElapsed() {
        return $this->elapsed;
    }

    public function setElapsed($elapsed) {
        $this->elapsed = $elapsed;
    }

    public function getRate() {
        return $this->rate;
    }

    public function setRate($rate) {
        $this->rate = $rate;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getCfopId() {
        return $this->cfopId;
    }

    public function setCfopId($cfopId) {
        $this->cfopId = $cfopId;
    }
}

?>
