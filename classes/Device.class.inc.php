<?php
class Device
{
	private $db;

    const STATUS_TYPE_DEVICE=1;
	private $deviceId;
	private $shortName;
	private $full_name;
	private $location;
	private $description; 
	private $status;
	private $deviceToken;
	private $unauthorizedUser;
    private $loggedUser;
    private $ldap_group;

	public function __construct(PDO $db)
	{
		$this->db = $db;
		$this->shortName = "";
		$this->full_name = "";
		$this->location= "";
		$this->description= "";
		$this->deviceId = 0;
		$this->status = 1;
	}
	
	public function __destruct()
	{
	}

    /**Create a new Device object and insert it into the database
     * @param $dn
     * @param $name
     * @param $location
     * @param $description
     * @param $status
     */
    public function create($dn, $name,$location, $description, $status)
	{
		$this->shortName = $dn;
		$this->full_name = $name;
		$this->location = $location;
		$this->description = $description;
		$this->status = $status;
		$this->deviceToken = md5(uniqid(mt_rand(), true));
		// TODO this sql query is not running
		$queryAddDevice = "INSERT INTO device (device_name,location,description,full_device_name,status_id,device_token) VALUES(:device_name,:location,:description,:full_device_name,:status_id,:device_token)";
        $addDevicePrep = $this->db->prepare($queryAddDevice);
        $addDevicePrep->execute(array(":device_name"=>$dn,":location"=>$location,":description"=>$description,":full_device_name"=>$name,":status_id"=>$status,":device_token"=>$this->deviceToken));
        
		$this->deviceId = $this->db->lastInsertId();

        //Add device rates rows to device rates table with default value of 0 for all values
        $ratesArr = Rate::getAllRates($this->db);
        foreach ($ratesArr as $id => $rateInfo) {
            $queryAddRates = "INSERT INTO device_rate (rate,device_id,rate_id, min_use_time, rate_type_id)VALUES(0,:device_id,:rate_id,0,0)";
            $addRatesPrep = $this->db->prepare($queryAddRates);
            $addRatesPrep->execute(array(":device_id"=>$this->deviceId,":rate_id"=>$rateInfo["id"]));
        }
        
        log::log_message("Created device '$dn'");
	}

    /** Load device from database into object given an authKey or id
     * @param $id
     * @param int $authKey
     */
    public function load($id,$authKey=0)
	{
		$queryDeviceInfo = "SELECT * FROM device WHERE id=:id OR (device_token=:device_token AND device_token!=\"0\")";
        $deviceInfoPrep = $this->db->prepare($queryDeviceInfo);
		$deviceInfoPrep->execute(array(':id'=>$id,':device_token'=>$authKey));
        $deviceInfoArr = $deviceInfoPrep->fetch(PDO::FETCH_ASSOC);
        if($deviceInfoArr) {
            $this->shortName = $deviceInfoArr["device_name"];
            $this->full_name = $deviceInfoArr["full_device_name"];
            $this->location = $deviceInfoArr["location"];
            $this->description = $deviceInfoArr["description"];
            $this->status = $deviceInfoArr["status_id"];
            $this->deviceToken = $deviceInfoArr["device_token"];
            $this->loggedUser = $deviceInfoArr["loggeduser"];
            $this->unauthorizedUser = $deviceInfoArr["unauthorized"];
            $this->deviceId = $deviceInfoArr['id'];
            $this->ldap_group = $deviceInfoArr['ldap_group'];
        }
	}


    /**
     * Update device object in database with getters and setters
     */
    public function update()
	{
		$queryUpdateDevice = "UPDATE device SET device_name=:device_name, location=:location,description=:description,full_device_name=:full_device_name, status_id=:status_id, ldap_group=:ldap_group WHERE id=:id";
        $updateDevicePrep = $this->db->prepare($queryUpdateDevice);
        $updateDevicePrep->execute(array(":device_name"=>$this->shortName,":location"=>$this->location,":description"=>$this->description,":full_device_name"=>$this->full_name,":status_id"=>$this->status,":id"=>$this->deviceId,":ldap_group"=>$this->ldap_group));
	}

	public function updateLastTick($username="")
	{
		if($username==""){
			$loggeduser = 0;
		} else {
			$loggeduser = -1;
		}
		$queryUpdateLastTick = "UPDATE device SET lasttick=NOW(), loggeduser=:loggeduser, unauthorized=:username WHERE id=:id";
		$updateLastTick = $this->db->prepare($queryUpdateLastTick);
        $updateLastTick->execute(array(':username'=>$username,':id'=>$this->deviceId,':loggeduser'=>$loggeduser));
	}

    /**Check if device with deviceName alrady exists
     * @param $deviceName
     * @return int
     */
    public function exists($deviceName)
	{
		$queryDeviceCount = "SELECT COUNT(*) AS num_devices FROM device WHERE device_name=:device_name";
		$deviceCount = $this->db->prepare($queryDeviceCount);
        $deviceCount->execute(array(':device_name'=>$deviceName));
        $deviceCountArr = $deviceCount->fetch(PDO::FETCH_ASSOC);
		if($deviceCountArr["num_devices"] > 0)
		{
			return 1;
		}
		else 
		{
			return 0;
		}
	}

	/**List all devices
	 * @param PDO $db
	 * @return array
	 */
    public static function getAllDevices($db)
    {
        $queryAllDevices = "SELECT id, device_name, full_device_name, status_id FROM device ORDER BY full_device_name";
        $allDevices = $db->query($queryAllDevices);
        $allDevicesArr = $allDevices->fetchAll(PDO::FETCH_ASSOC);

        return $allDevicesArr;
    }

	/**
	 * @param PDO $db
	 * @param int $id
	 * @return mixed
	 */
    public static function getDevicesInSameRoom($db, $id){
    	$query = "select id from device where location = (select location from device where id=? limit 1) and id != ?";
    	$devices = $db->prepare($query);
    	$devices->execute(array($id, $id));
		return $devices->fetchAll(PDO::FETCH_ASSOC);
	}

    public static function getAllDevicesStatus($db)
    {
        $queryDevicesUse = "SELECT d.full_device_name, d.location, u.user_name, d.loggeduser,u.first, u.last, TIMESTAMPDIFF(SECOND, lasttick, NOW()) AS lastseen , unauthorized FROM users u RIGHT JOIN device d ON u.id=d.loggeduser WHERE d.status_id=1 OR d.status_id=2 order by d.full_device_name";
        $devicesUse = $db->prepare($queryDevicesUse);
        $devicesUse->execute();
        $devicesUseArr = $devicesUse->fetchAll(PDO::FETCH_ASSOC);
        return $devicesUseArr;
    }
    /**Get rates list for device
     * @return array
     */
    public function getRates()
    {
        $queryDeviceRates = "SELECT dr.rate, dr.id, dr.rate_id, dr.min_use_time, r.rate_name, dr.rate_type_id FROM device_rate dr, rates r WHERE r.id=dr.rate_id AND dr.device_id=:device_id";
        $deviceRatesPrep = $this->db->prepare($queryDeviceRates);
        $deviceRatesPrep->execute(array(":device_id"=>$this->deviceId));
        $deviceRatesArr = $deviceRatesPrep->fetchAll(PDO::FETCH_ASSOC);

        return $deviceRatesArr;
    }

    /**Update this device's rate
     * @param $rateId
     * @param $rate
     * @param $minTime
     * @param $rateTypeId
     */
    public function updateRate($rateId, $rate, $minTime, $rateTypeId)
    {
        $queryUpdateDeviceRate = "UPDATE device_rate SET rate=:rate, min_use_time=:mintime, rate_type_id=:rate_type_id WHERE rate_id=:rate_id AND device_id=:device_id";
        $updateDeviceRatePrep = $this->db->prepare($queryUpdateDeviceRate);
        $updateDeviceRatePrep->execute(array(":rate"=>$rate,":mintime"=>$minTime,":rate_id"=>$rateId,":device_id"=>$this->deviceId,":rate_type_id"=>$rateTypeId));
    }

    public static function deviceStatusList($db)
    {
        $queryDeviceStatusList = "SELECT * FROM status WHERE type=:type";
        $deviceStatusList = $db->prepare($queryDeviceStatusList);
        $deviceStatusList->execute(array('type'=>Device::STATUS_TYPE_DEVICE));
        $deviceStatusListArr = $deviceStatusList->fetchAll(PDO::FETCH_ASSOC);

        return $deviceStatusListArr;
    }

    //Getters and setters for device
    public function setDeviceId($id){
	    if($this->deviceId != $id){
		    $this->deviceId = $id;
			log::log_message("Set id of device '".$this->shortName."' to $id");
		}
    }
    public function getId()
	{
		return $this->deviceId;
	}

	public function setShortName($dn)
	{
		if($this->shortName != $dn){
			log::log_message("Set short name of device '".$this->shortName."' to '$dn'");
			$this->shortName = $dn;
		}
	}

	public function getShortName()
	{
		return $this->shortName;
	}

	public function getLDAPGroup(){
		return $this->ldap_group;
	}
	public function setLDAPGroup($ldap_group){
		if($this->ldap_group != $ldap_group){
			$this->ldap_group = $ldap_group;
			log::log_message("Set LDAP group of device '".$this->shortName."' to $ldap_group");
		}
	}

	public function setFullName($name)
	{
		if($this->full_name != $name){
			$this->full_name = $name;
			log::log_message("Set full name of device '".$this->shortName."' to '$name'");
		}
	}
	
	public function getFullName()
	{
		return $this->full_name;
	}

	public function setLocation($location)
	{
		if($this->location != $location){
			$this->location = $location;
			log::log_message("Set location of device '".$this->shortName."' to $location");
		}
	}

	public function getLocation()
	{
		return $this->location;
	}

	public function getStatus()
	{
		return $this->status;
	}	

	public function setStatus($status)
	{
		if($this->status != $status){
			$this->status = $status;
			log::log_message("Set status of device '".$this->shortName."' to $status");
		}
	}

	public function setDescription($description)
	{
		if($this->description != $description){
			$this->description = $description;
			log::log_message("Set description of device '".$this->shortName."' to '$description'");
		}
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getDeviceToken()
	{
		return $this->deviceToken;
	}

}
?>
