<?php
class Device
{


    const STATUS_TYPE_DEVICE=1;
    private $sqlDataBase;
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

	public function __construct(PDO $sqlDataBase)
	{
		$this->sqlDataBase = $sqlDataBase;
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
    public function CreateDevice($dn, $name,$location, $description, $status)
	{
		$this->shortName = $dn;
		$this->full_name = $name;
		$this->location = $location;
		$this->description = $description;
		$this->status = $status;
		$this->deviceToken = md5(uniqid(mt_rand(), true));
		// TODO this sql query is not running
		$queryAddDevice = "INSERT INTO device (device_name,location,description,full_device_name,status_id,device_token) VALUES(:device_name,:location,:description,:full_device_name,:status_id,:device_token)";
        $addDevicePrep = $this->sqlDataBase->prepare($queryAddDevice);
        $addDevicePrep->execute(array(":device_name"=>$dn,":location"=>$location,":description"=>$description,":full_device_name"=>$name,":status_id"=>$status,":device_token"=>$this->deviceToken));
        
		$this->deviceId = $this->sqlDataBase->lastInsertId();

        //Add device rates rows to device rates table with default value of 0 for all values
        $rate= new Rate($this->sqlDataBase);
        $ratesArr = $rate->GetRates();
        foreach ($ratesArr as $id => $rateInfo) {
            $queryAddRates = "INSERT INTO device_rate (rate,device_id,rate_id, min_use_time, rate_type_id)VALUES(0,:device_id,:rate_id,0,0)";
            $addRatesPrep = $this->sqlDataBase->prepare($queryAddRates);
            $addRatesPrep->execute(array(":device_id"=>$this->deviceId,":rate_id"=>$rateInfo["id"]));
        }
	}

    /** Load device from database into object given an authKey or id
     * @param $id
     * @param int $authKey
     */
    public function LoadDevice($id,$authKey=0)
	{
		$queryDeviceInfo = "SELECT * FROM device WHERE id=:id OR (device_token=:device_token AND device_token!=\"0\")";
        $deviceInfoPrep = $this->sqlDataBase->prepare($queryDeviceInfo);
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
    public function UpdateDevice()
	{
		$queryUpdateDevice = "UPDATE device SET device_name=:device_name, location=:location,description=:description,full_device_name=:full_device_name, status_id=:status_id, ldap_group=:ldap_group WHERE id=:id";
        $updateDevicePrep = $this->sqlDataBase->prepare($queryUpdateDevice);
        $updateDevicePrep->execute(array(":device_name"=>$this->shortName,":location"=>$this->location,":description"=>$this->description,":full_device_name"=>$this->full_name,":status_id"=>$this->status,":id"=>$this->deviceId,":ldap_group"=>$this->ldap_group));
	}

	public function UpdateLastTick($username="")
	{
		if($username==""){
			$loggeduser = 0;
		} else {
			$loggeduser = -1;
		}
		$queryUpdateLastTick = "UPDATE device SET lasttick=NOW(), loggeduser=:loggeduser, unauthorized=:username WHERE id=:id";
		$updateLastTick = $this->sqlDataBase->prepare($queryUpdateLastTick);
        $updateLastTick->execute(array(':username'=>$username,':id'=>$this->deviceId,':loggeduser'=>$loggeduser));
	}

    /**Check if device with deviceName alrady exists
     * @param $deviceName
     * @return int
     */
    public function Exists($deviceName)
	{
		$queryDeviceCount = "SELECT COUNT(*) AS num_devices FROM device WHERE device_name=:device_name";
		$deviceCount = $this->sqlDataBase->prepare($queryDeviceCount);
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

    /**Set currently logged user
     * @param $userId
     * @param string $unauthorizedUser
     */
    public function SetLoggedUser($userId,$unauthorizedUser="")
	{
		$updateLoggedUser = "UPDATE device SET loggeduser=:loggeduser, unauthorized=:unauthorized WHERE id=:id";

		$loggedUser = $this->sqlDataBase->prepare($updateLoggedUser);
        $loggedUser->execute(array(':loggeduser'=>$userId,':unauthorized'=>$unauthorizedUser,':id'=>$this->deviceId));

	}

    /**List all devices
     * @return array
     */
    public function GetDevicesList()
    {
        $queryAllDevices = "SELECT id, device_name, full_device_name, status_id FROM device ORDER BY full_device_name";
        $allDevices = $this->sqlDataBase->query($queryAllDevices);
        $allDevicesArr = $allDevices->fetchAll(PDO::FETCH_ASSOC);

        return $allDevicesArr;
    }

    public function GetDevicesInUse()
    {
        $queryDevicesUse = "SELECT d.full_device_name, d.location, u.user_name, d.loggeduser,u.first, u.last, TIMESTAMPDIFF(SECOND, lasttick, NOW()) AS lastseen , unauthorized FROM users u RIGHT JOIN device d ON u.id=d.loggeduser WHERE d.status_id=1 OR d.status_id=2 order by d.full_device_name";
        $devicesUse = $this->sqlDataBase->prepare($queryDevicesUse);
        $devicesUse->execute();
        $devicesUseArr = $devicesUse->fetchAll(PDO::FETCH_ASSOC);
        return $devicesUseArr;
    }
    /**Get rates list for device
     * @return array
     */
    public function GetRatesList()
    {
        $queryDeviceRates = "SELECT dr.rate, dr.id, dr.rate_id, dr.min_use_time, r.rate_name, dr.rate_type_id FROM device_rate dr, rates r WHERE r.id=dr.rate_id AND dr.device_id=:device_id";
        $deviceRatesPrep = $this->sqlDataBase->prepare($queryDeviceRates);
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
    public function UpdateDeviceRate($rateId, $rate, $minTime, $rateTypeId)
    {
        $queryUpdateDeviceRate = "UPDATE device_rate SET rate=:rate, min_use_time=:mintime, rate_type_id=:rate_type_id WHERE rate_id=:rate_id AND device_id=:device_id";
        $updateDeviceRatePrep = $this->sqlDataBase->prepare($queryUpdateDeviceRate);
        $updateDeviceRatePrep->execute(array(":rate"=>$rate,":mintime"=>$minTime,":rate_id"=>$rateId,":device_id"=>$this->deviceId,":rate_type_id"=>$rateTypeId));
    }

    /**Get device rate by rate_id
     * @param $rateId
     * @return mixed
     */
    public function GetRate($rateId)
    {
        $queryRateForDevice = "SELECT rate FROM device_rate WHERE device=:device_id AND rate_id=:rate_id";
        $rateForDevicePrep = $this->sqlDataBase->prepare($queryRateForDevice);
        $rateForDevicePrep->execute((array(":device_id"=>$this->deviceId,":rate_id"=>$rateId)));
        $rateForDeviceArr = $rateForDevicePrep->fetch(PDO::FETCH_ASSOC);

        return $rateForDeviceArr['rate'];
    }

    public function DeviceStatusList()
    {
        $queryDeviceStatusList = "SELECT * FROM status WHERE type=:type";
        $deviceStatusList = $this->sqlDataBase->prepare($queryDeviceStatusList);
        $deviceStatusList->execute(array('type'=>Device::STATUS_TYPE_DEVICE));
        $deviceStatusListArr = $deviceStatusList->fetchAll(PDO::FETCH_ASSOC);

        return $deviceStatusListArr;
    }

    //Getters and setters for device
    public function SetDeviceId($id){
	    $this->deviceId = $id;
    }
    public function GetDeviceId()
	{
		return $this->deviceId;
	}

	public function SetShortName($dn)
	{
		$this->shortName = $dn;
	}

	public function getShortName()
	{
		return $this->shortName;
	}

	public function GetLDAPGroup(){
		return $this->ldap_group;
	}
	public function SetLDAPGroup($ldap_group){
		$this->ldap_group = $ldap_group;
	}

	public function SetFullName($name)
	{
		$this->full_name = $name;
	}
	
	public function GetFullName()
	{
		return $this->full_name;
	}

	public function SetLocation($location)
	{
		$this->location = $location;
	}

	public function GetLocation()
	{
		return $this->location;
	}

	public function GetStatus()
	{
		return $this->status;
	}	

	public function SetStatus($status)
	{
		$this->status = $status;
	}

	public function SetDescription($description)
	{
		$this->description = $description;
	}

	public function GetDescription()
	{
		return $this->description;
	}

	public function GetDeviceToken()
	{
		return $this->deviceToken;
	}

	public function GetLoggedUser()
	{
		return $this->loggedUser();
	}
	
	public function GetUnauthorizedUser()
	{
		return $this->unauthorizedUser;
	}

}
?>
