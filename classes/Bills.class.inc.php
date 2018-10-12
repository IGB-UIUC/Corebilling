<?php

class Bills
{
    private $db;
    
    private $groupBy;
    private $userId;
    private $deviceId;

    const CONTINUOUS_RATE = 1;
    const MONTHLY_RATE = 2;
    const GROUP_CFOP = 1;
    const GROUP_USER = 2;
    const GROUP_DEVICE = 3;
    const GROUP_DEVICE_USER = 4;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->groupBy = 0; //Do not group by default
        $this->userId = 0;
        $this->deviceId = 0;
    }

    public function __destruct()
    {

    }

    /**
     * Return one month of bills
     * @param $year
     * @param $month
     * @param $rateType
     * @return mixed
     */
    public function GetMonthCharges($year, $month, $rateType)
    {
        $pdoParameters = array();
        $querySelectClauseMonthUsage = "SELECT s.id, uc.cfop,s.cfop_id, s.rate, s.user_id, s.device_id,  d.full_device_name, u.user_name, s.start, s.stop,CONCAT(u.first,' ',u.last) as full_name, s.description,r.rate_name, dr.min_use_time, g.group_name, dr.rate_type_id, de.department_name ";
        $queryTablesClauseMonthUsage = " FROM `session` s 
left join users u on u.id=s.user_id
left join device_rate dr on dr.rate_id=u.rate_id and dr.device_id=s.device_id
left join device d on d.id=s.device_id
LEFT JOIN groups g ON (g.id=u.group_id)
left join rates r on r.id=u.rate_id
LEFT JOIN user_cfop uc ON (uc.id=s.cfop_id AND uc.default_cfop=1) 
left join departments de on de.id=u.department_id";
        $queryWhereClauseMonthUsage = " WHERE MONTH(start)=:month AND YEAR(start)=:year AND dr.rate_type_id=:rate_type_id";
        $pdoParameters[':year'] = $year;
        $pdoParameters[':month'] = $month;
        $pdoParameters[':rate_type_id'] = $rateType;

        if ($this->userId) {
            $queryWhereClauseMonthUsage .= " AND s.user_id=:user_id";
            $pdoParameters[':user_id'] = $this->userId;
        }

        if ($this->deviceId) {
            $queryWhereClauseMonthUsage .= " AND s.device_id=:device_id";
            $pdoParameters[':device_id'] = $this->deviceId;
        }

        switch ($this->groupBy) {
            case self::GROUP_CFOP:
                $queryWhereClauseMonthUsage .= " GROUP BY s.cfop";
                $querySelectClauseMonthUsage .= ", s.elapsed";
                break;
            case self::GROUP_DEVICE:
                $querySelectClauseMonthUsage .= ", SUM(s.elapsed) as elapsed";
                $queryWhereClauseMonthUsage .= " GROUP BY s.device_id";
                break;
            case self::GROUP_USER:
                $queryWhereClauseMonthUsage .= " GROUP BY s.user_id";
                $querySelectClauseMonthUsage .= ", s.elapsed";
                break;
            default:
                $querySelectClauseMonthUsage .= ", s.elapsed";
        }

        $queryMonthUsagePrepare = $this->db->prepare($querySelectClauseMonthUsage . $queryTablesClauseMonthUsage . $queryWhereClauseMonthUsage, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $queryMonthUsagePrepare->execute($pdoParameters);
        $monthUsage = $queryMonthUsagePrepare->fetchAll(PDO::FETCH_ASSOC);

        return $monthUsage;
    }
    
    public function GetMonthsCharges($startyear, $startmonth, $endyear, $endmonth, $rateType){
	    $pdoParameters = array();
        $querySelectClauseMonthUsage = "SELECT s.id, uc.cfop,s.cfop_id, s.rate, s.user_id, s.device_id,  d.full_device_name, u.user_name, s.start, s.stop,CONCAT(u.first,' ',u.last) as full_name, s.description,r.rate_name, dr.min_use_time, g.group_name, dr.rate_type_id, de.department_name ";
        $queryTablesClauseMonthUsage = " FROM 
	`session` s
	left join users u on u.id=s.user_id
	left join device d on d.id=s.device_id
	left join device_rate dr on (dr.device_id=d.id and dr.rate_id=u.rate_id) 
	LEFT JOIN groups g ON (g.id=u.group_id)
	left join rates r on r.id=s.rate_id
	LEFT JOIN user_cfop uc ON (uc.id=s.cfop_id AND uc.default_cfop=1) 
	left join departments de on de.id=u.`department_id`";
        $queryWhereClauseMonthUsage = " WHERE ((MONTH(start)>=:startmonth AND YEAR(start)=:startyear) OR YEAR(start)>:startyear) AND ((MONTH(start)<=:endmonth AND YEAR(start)=:endyear) OR YEAR(start)<:endyear) AND dr.rate_type_id=:rate_type_id";
        $pdoParameters[':startyear'] = $startyear;
        $pdoParameters[':startmonth'] = $startmonth;
        $pdoParameters[':endyear'] = $endyear;
        $pdoParameters[':endmonth'] = $endmonth;
        $pdoParameters[':rate_type_id'] = $rateType;

        if ($this->userId) {
            $queryWhereClauseMonthUsage .= " AND s.user_id=:user_id";
            $pdoParameters[':user_id'] = $this->userId;
        }

        if ($this->deviceId) {
            $queryWhereClauseMonthUsage .= " AND s.device_id=:device_id";
            $pdoParameters[':device_id'] = $this->deviceId;
        }

        switch ($this->groupBy) {
            case self::GROUP_CFOP:
                $queryWhereClauseMonthUsage .= " GROUP BY s.cfop";
                $querySelectClauseMonthUsage .= ", s.elapsed";
                break;
            case self::GROUP_DEVICE:
                $querySelectClauseMonthUsage .= ", SUM(s.elapsed) as elapsed";
                $queryWhereClauseMonthUsage .= " GROUP BY s.device_id";
                break;
            case self::GROUP_USER:
                $queryWhereClauseMonthUsage .= " GROUP BY s.user_id";
                $querySelectClauseMonthUsage .= ", s.elapsed";
                break;
            case self::GROUP_DEVICE_USER:
            	$querySelectClauseMonthUsage .= ", SUM(s.elapsed) as elapsed";
            	$queryWhereClauseMonthUsage .= " GROUP BY s.user_id, s.device_id";
            	break;
            default:
                $querySelectClauseMonthUsage .= ", s.elapsed";
        }

        $queryMonthUsagePrepare = $this->db->prepare($querySelectClauseMonthUsage . $queryTablesClauseMonthUsage . $queryWhereClauseMonthUsage, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $queryMonthUsagePrepare->execute($pdoParameters);
        $monthUsage = $queryMonthUsagePrepare->fetchAll(PDO::FETCH_ASSOC);

        return $monthUsage;
    }


    /**Return available months for billing
     * @return mixed
     */
    public function GetAvailableBillingMonths()
    {
        $queryAvailableMonths = "SELECT DISTINCT DATE_FORMAT(start,'%M %Y') AS mon_yr, MONTH(start) AS month, YEAR(start) AS year FROM session ORDER BY start DESC";
        $availableMonths = $this->db->query($queryAvailableMonths);
        return $availableMonths;
    }

    /**Receive an array of session ids, set each one to the user's default cfop
     * @param $sessionIdArray
     */
    public function SetToDefaultCFOP($sessionIdArray)
    {
        //Load user cfop and session objects
        $userCfop = new UserCfop($this->db);
        $billSession = new Session($this->db);

        //Cycle through each session bill for the month
        foreach($sessionIdArray as $sessionId)
        {
            //Update each session with the user's default CFOP
            $billSession->load($sessionId);
            $userDefaultCfopId = $userCfop->loadDefaultCfop($billSession->getUserId());
            $billSession->setCfopId($userDefaultCfopId);
            $billSession->update();
        }
    }

    /**Get excel file from year month and rate type
     * @param $year
     * @param $month
     * @param $rateType
     */
    public function GetExcelCharges($year, $month, $rateType)
    {
        $monthUsageArr = $this->GetMonthCharges($year, $month, $rateType);
        Report::create_excel_2007_report($monthUsageArr, "facilities_billing");
    }

    /**Calculate total to bill for given rate type
     * @param $elapsed
     * @param $rateTypeId
     * @param $rate
     * @param $min_use_time
     * @return mixed
     */
    public function CalcTotal($elapsed, $rateTypeId, $rate, $min_use_time)
    {
        switch($rateTypeId)
        {
            case Bills::CONTINUOUS_RATE:
                $total = $this->CalcContinuous($elapsed,$rate,$min_use_time);
                break;
            case Bills::MONTHLY_RATE:
                $total = $this->CalcMonthly($elapsed,$rate,$min_use_time);
                break;
        }

        return $total;
    }

    /**Calculate continuous rate
     * @param $elapsed
     * @param $rate
     * @param $min_use_time
     * @return mixed
     */
    private function CalcContinuous($elapsed,$rate,$min_use_time)
    {
        if($min_use_time > $elapsed)
        {
            $elapsed = $min_use_time;
        }

        return $elapsed * $rate;
    }

    /**Calculate monthly rate
     * @param $elapsed
     * @param $rate
     * @param $min_use_time
     * @return mixed
     */
    private function CalcMonthly($elapsed, $rate, $min_use_time)
    {
        return $rate * 60;
    }
    /**Set which column you would like to group the bills by
     * Options available are GROUP_CFOP, GROUP_USER, GROUP_DEVICE
     * @param mixed $groupBy
     */
    public function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;
    }


    /**
     * @param mixed $deviceId
     */
    public function setDeviceid($deviceId)
    {
        $this->deviceId = $deviceId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }
}


?>
