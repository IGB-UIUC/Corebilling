<?php
/**
 * Created by PhpStorm.
 * User: nevoband
 * Date: 2/11/2015
 * Time: 2:39 PM
 * Used for migrating from the old coreapp schema to this new one
 */

class Migration
{
    //Databases information
    private $sourceDb = 'igb_instru';
    private $sourceUser = 'igb_instru_user';
    private $sourcePass = 'igb123';
    private $sourceHost = 'localhost';

    private $destDb = 'biotech_instru';
    private $destUser = 'flowcyt_user';
    private $destPass = 'Dlr%8679';
    private $destHost = 'localhost';

    private $sourcePDOHandler;
    private $destPDOHandler;
    //Tables ids
    private $device = array();
    private $user = array();
    private $rate = array();
    private $groups = array();
    private $departments = array();

    //Static arrays
    private$status = array();
    private$userType = array();
    private$ratesType = array();

    public function __construct()
    {
        //Static arrays initializing
        $status[1] = 1;
        $status[2] = 2;
        $status[3] = 3;
        $status[4] = 4;
        $status[5] = 5;
        $status[6] = 6;
        $status[7] = 7;

        $userType[1] = 1;
        $userType[2] = 2;
        $userType[3] = 3;

        $ratesType[1] = 1;
        $ratesType[2] = 2;
    }

    public function __destruct()
    {

    }
    public function Migrate()
    {
        //Start by migrating device rates
        $this->sourcePDOHandler = $this->GetPDO($this->sourceUser, $this->sourcePass, $this->sourceDb, $this->sourceHost);
        $this->destPDOHandler = $this->GetPDO($this->destUser, $this->destPass, $this->destDb, $this->destHost);

        if($this->sourcePDOHandler && $this->destPDOHandler)
        {

        }
        else
        {
            return 0;
        }
    }

    private function MigrateTable($sourceTableName,$destTableName,$sourceColumnNames,$destColumnNames)
    {
        $newIncrement = 1;
        $querySourceDeviceRates = "SELECT * FROM devicerate";
        $sourceDeviceRatesHandler = $this->sourcePDOHandler->prepare($querySourceDeviceRates);
        $sourceDeviceRatesHandler->execute();

        foreach($sourceDeviceRatesHandler->fetch(PDO::FETCH_ASSOC) as $sourceDeviceRate)
        {
            $queryDestDeviceRates = "INSERT INTO device_rate (id,rate,device_id,rate_id,min_use_time,rate_type_id)VALUES (:id, :rate,:device_id,:rate_id,:min_use_time,:rate_type_id)";
            $destDeviceRatesHandler = $this->destPDOHandler->prepare($queryDestDeviceRates);
            $destDeviceRatesHandler->execute(array(':id'=>$sourceDeviceRate['ID'],':rate'=>$sourceDeviceRate['rate'],':device_id'=>$sourceDeviceRate['deviceid'],':rate_id'=>$sourceDeviceRate['rateid'],':min_use_time'=>0));

            //Make sure id is set to 1 above highest ids
            if($newIncrement < ($sourceDeviceRate['ID']+1))
            {
                $newIncrement = ($sourceDeviceRate['ID']+1);
            }
        }

        $queryDestDeviceRatesIncrement = "ALTER TABLE device_rate AUTO_INCREMENT = $newIncrement";
        $destDeviceRatesIncrement = $this->destPDOHandler->prepare($queryDestDeviceRatesIncrement);
        $destDeviceRatesIncrement->execute();

    }

    private function GetPDO($user, $pass, $db, $host)
    {
        try {
            $pdoHandler = new PDO("mysql:host=" . $host . ";dbname=" . $db, $user, $pass);
            return $pdoHandler;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return 0;

        }
    }

    /**
     * @param string $sourceDb
     */
    public function setSourceDb($sourceDb)
    {
        $this->sourceDb = $sourceDb;
    }

    /**
     * @param string $sourceUser
     */
    public function setSourceUser($sourceUser)
    {
        $this->sourceUser = $sourceUser;
    }

    /**
     * @param string $sourcePass
     */
    public function setSourcePass($sourcePass)
    {
        $this->sourcePass = $sourcePass;
    }

    /**
     * @param string $sourceHost
     */
    public function setSourceHost($sourceHost)
    {
        $this->sourceHost = $sourceHost;
    }

    /**
     * @param string $destDb
     */
    public function setDestDb($destDb)
    {
        $this->destDb = $destDb;
    }

    /**
     * @param string $destUser
     */
    public function setDestUser($destUser)
    {
        $this->destUser = $destUser;
    }

    /**
     * @param string $destPass
     */
    public function setDestPass($destPass)
    {
        $this->destPass = $destPass;
    }

    /**
     * @param string $destHost
     */
    public function setDestHost($destHost)
    {
        $this->destHost = $destHost;
    }
}
?>