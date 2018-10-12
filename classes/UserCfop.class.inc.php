<?php
class UserCfop {
    const DEFAULT_CFOP=1,NON_DEFAULT_CFOP=0,ACTIVE_CFOP=1,NON_ACTIVE_CFOP=0;

    private $userId;
    private $cfop;
    private $description;
    private $userCfopId;
    private $active;
    private $default;
    private $createdDate;
    private $db;

    public function __construct(PDO $db)
    {
            $this->db = $db;
            $this->userCfopId = 0;
            $this->default = 1;
            $this->active = 1;
    }

    public function __destruct()
    {

    }

    /** Create CFOP to charge
     * @param $userId
     * @param $cfop
     * @param $description
     */
    public function create($userId, $cfop, $description)
    {
        $this->userId = $userId;
        $this->cfop = $cfop;
        $this->description = $description;

        $insertUserCfopl = "INSERT INTO user_cfop (user_id,cfop,description,active,default_cfop,created)VALUES(:user_id,:cfop,:description,:active,:default_cfop,NOW())";
        $userCfoplInfo = $this->db->prepare($insertUserCfopl);
        $userCfoplInfo->execute(array(':user_id'=>$this->userId,':cfop'=>$this->cfop,':description'=>$this->description,':active'=>$this->active,':default_cfop'=>UserCfop::DEFAULT_CFOP));
        $this->userCfopId =$this->db->lastInsertId();
        $this->load($this->userCfopId);
        $this->setAsDefaultCFOP();
        log::log_message("Added CFOP '$cfop' for user $userId");
    }

    /**Load User CFOP from cfop id
     * @param $userCfopId
     */
    public function load($userCfopId)
    {
        $queryUserCfop = "SELECT * FROM user_cfop WHERE id=:user_cfop_id";
        $userCfopInfo = $this->db->prepare($queryUserCfop);
        $userCfopInfo->execute(array(':user_cfop_id'=>$userCfopId));
        $userCfopArr = $userCfopInfo->fetch(PDO::FETCH_ASSOC);
        $this->userId = $userCfopArr['user_id'];
        $this->cfop = $userCfopArr['cfop'];
        $this->description = $userCfopArr['description'];
        $this->createdDate = $userCfopArr['created'];
        $this->userCfopId = $userCfopId;
    }

    /**
     * Set this cfop as default cfop for user
     */
    public function setAsDefaultCFOP()
    {
	    if(!$this->default){
		    log::log_message("Set default CFOP for user ".$this->userId." to '".$this->cfop."'");
	    }
        //mark all other user cfopls as not default
        $queryRemoveDefault = "UPDATE user_cfop SET default_cfop=".UserCfop::NON_DEFAULT_CFOP." WHERE user_id=:user_id";
        $removeDefault = $this->db->prepare($queryRemoveDefault);
        $removeDefault->execute(array(':user_id'=>$this->userId));

        //mark current cfop as default
        $querySetDefault = "UPDATE user_cfop SET default_cfop=".UserCfop::DEFAULT_CFOP." WHERE id=:user_cfop_id";
        $setDefault = $this->db->prepare($querySetDefault);
        $setDefault->execute(array(':user_cfop_id'=>$this->userCfopId));
    }

    /**Load default CFOPfor given user id
     * @param $userId
     * @return int
     */
    public function loadDefaultCfop($userId)
    {
        $queryDefaultCfop = "SELECT id FROM user_cfop WHERE user_id=:user_id AND default_cfop=1";
        $defaultCfop = $this->db->prepare($queryDefaultCfop);
        $defaultCfop->execute(array(':user_id'=>$userId));
        $defaultCfopArr = $defaultCfop->fetch(PDO::FETCH_ASSOC);

        if($defaultCfop->rowCount() > 0)
        {
            $userCfopId = $defaultCfopArr['id'];
            $this->load($userCfopId);
            return $userCfopId;
        }
        else{
            return 0;
        }
    }

    /** List available CFOPs for user id which are currently active
     * @param $userId
     * @return array
     */
    public static function getAllCFOPs($db,$userId)
    {
        $queryListCfops = "SELECT * FROM user_cfop WHERE user_id=:user_id AND active=".UserCfop::ACTIVE_CFOP;
        $listCfops = $this->db->prepare($queryListCfops);
        $listCfops->execute(array(':user_id'=>$userId));
        $listCfopsArr = $listCfops->fetchAll(PDO::FETCH_ASSOC);

        return $listCfopsArr;
    }

    /**Clean up and add spaces between numbers
     * @param $cfop
     * @return mixed|string
     */
    public static function formatCfop($cfop)
    {
	    //TODO overhaul this
//         $replace_array = array(" ");
        $cfop = str_replace("-", "", $cfop);
        if(strlen($cfop)<=19){
	        $cfop = substr($cfop, 0, 1) . "-" . substr($cfop, 1, 6) . "-" . substr($cfop, 7, 6) . "-" . substr($cfop, 13, 6);
		} else {
			$cfop = substr($cfop, 0, 1) . "-" . substr($cfop, 1, 6) . "-" . substr($cfop, 7, 6) . "-" . substr($cfop, 13, 6)."-".substr($cfop, 19);
		}
        return $cfop;
    }

    // Getters and setters

    /**
     * @param mixed $cfop
     */
    public function setCfop($cfop)
    {
        $this->cfop = $cfop;
    }

    /**
     * @return mixed
     */
    public function getCfop()
    {
        return $this->cfop;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

} 