<?php
class User
{

    const ACTIVE = 5, HIDDEN = 6, DISABLED = 7;
    const STATUS_TYPE_USER=2;

	private $userId;
	private $username;
	private $first;
	private $last;
	private $email;
	private $departmentId;
	private $groupId;
	private $rateId;
	private $statusid;
	private $userRoleId;
	private $sqlDataBase;
	private $dateAdded;
	private $secureKey;
    private $userCfop;
	
	public function __construct(PDO $sqlDataBase)
	{
		$this->sqlDataBase = $sqlDataBase;
		$this->userId=0;
		$this->username="";
		$this->first="";
		$this->last="";
		$this->email="";
		$this->departmentId=0;
		$this->groupId=0;
		$this->rateid=0;
		$this->statusid=0;
		$this->dateAdded="";
		$this->secureKey="";
        $this->userCfop = new UserCfop($this->sqlDataBase);
	}
	
	public function __destruct()
	{
		
	}

    /**Create User
     * @param $username
     * @param $first
     * @param $last
     * @param $email
     * @param $departmentId
     * @param $groupId
     * @param $rateId
     * @param $statusId
     * @param $userRoleId
     */
    public function CreateUser($username, $first, $last, $email,$departmentId,$groupId,$rateId,$statusId,$userRoleId)
	{
		$this->username = $username;
		$this->first = $first;
		$this->last = $last;
		$this->email = $email;
		$this->departmentId = $departmentId;
		$this->groupId = $groupId;
		$this->rateid = $rateId;
		$this->statusid = $statusId;
		$this->userRoleId = $userRoleId;
		$this->dateAdded = date('Y-m-d H:i:s');
		if($this->Exists($this->username)==0)
		{
			$queryAddUser = "INSERT INTO users (user_name, first,last,email,department_id,group_id,rate_id,status_id,date_added,secure_key,user_role_id)
			                    VALUES(:user_name,:first,:last,:email,:department_id,:group_id,:rate_id,:status_id,NOW(), MD5(RAND()),:user_role_id)";
			$addUserPrepare = $this->sqlDataBase->prepare($queryAddUser);
            $addUserPrepare->execute(array(':user_name'=>$this->username,':first'=>$this->first,':last'=>$this->last,':email'=>$this->email,':department_id'=>$this->departmentId,':group_id'=>$this->groupId,':rate_id'=>$rateId,':status_id'=>$statusId,':user_role_id'=>$this->userRoleId));
            $this->userId=$this->sqlDataBase->lastInsertId();
		}
	}

    /**Load user info from ldap by netid
     * @param $netid
     * @return array
     */
    public function LoadLdapUser($netid)
	{
		$info = LdapHelper::LoadIGBUser($netid);
		if($info['count']!=0)
		{
			$this->username=$info[0]["uid"][0];
			@list($firstName,$lastName) = explode(' ',$info[0]["cn"][0]);
			$this->first=$firstName;
			$this->last=$lastName;
			$this->email=$info[0]["mail"][0];
			if($info['count']>1)
			{
				return $info;
			}
			else
			{
				$info =  array();
				return $info;
			}
		}
		else
		{
			$info = array();
			return $info;
		}
	}

    /**Load user into this object
     * @param $id
     */
    public function LoadUser($id)
	{
		$queryUserInfo = "SELECT * FROM users WHERE id=:user_id";
		$userInfo=$this->sqlDataBase->prepare($queryUserInfo);
        $userInfo->execute(array(":user_id"=>$id));
        $userInfoArr = $userInfo->fetch(PDO::FETCH_ASSOC);
		$this->userId = $userInfoArr["id"];
		$this->username=$userInfoArr["user_name"];
		$this->first=$userInfoArr["first"];
		$this->last=$userInfoArr["last"];
		$this->email=$userInfoArr["email"];
		$this->departmentId=$userInfoArr["department_id"];
		$this->groupId=$userInfoArr["group_id"];
		$this->rateid=$userInfoArr["rate_id"];
		$this->statusid=$userInfoArr["status_id"];
		$this->userRoleId= $userInfoArr["user_role_id"];
		$this->dateAdded = $userInfoArr["date_added"];
		$this->secureKey = $userInfoArr['secure_key'];
	}

    /**
     * Update user into database based on changes made to this object
     */
    public function UpdateUser()
	{
		$queryUpdateUser = "UPDATE users SET
		                    user_name=:user_name,
		                    first=:first,
		                    last=:last,
		                    email=:email,
		                    department_id=:department_id,
		                    group_id=:group_id,
		                    rate_id=:rate_id,
		                    status_id=:status_id,
		                    user_role_id=:user_role_id
		                    WHERE id=:user_id";
        $updateUserPrep = $this->sqlDataBase->prepare($queryUpdateUser);
		$updateUserPrep->execute(array(':user_name'=>$this->username,':first'=>$this->first,':last'=>$this->last,':email'=>$this->email,':department_id'=>$this->departmentId,':group_id'=>$this->groupId,':rate_id'=>$this->rateid,':status_id'=>$this->statusid,':user_role_id'=>$this->userRoleId,':user_id'=>$this->userId));
	}

    /**Check if a user exists by netid
     * @param $username
     * @return int
     */
    public function Exists($username)
	{
		$queryUserName = "SELECT id FROM users WHERE user_name = :user_name";
		$userName = $this->sqlDataBase->prepare($queryUserName);
        $userName->execute(array(":user_name"=>$username));
        $userNameArr = $userName->fetch(PDO::FETCH_ASSOC);

		if($userName->rowCount() > 0)
		{
            return $userNameArr["id"];
		}
		else
		{
			return 0;
		}
		
	}

    /**
     * Update security key for user
     */
    public function UpdateSecureKey()
	{
		$queryUpdateSecureKey = "UPDATE users SET secure_key=MD5(RAND()) WHERE id = :user_id";
		$updateSecureKey = $this->sqlDataBase->prepare($queryUpdateSecureKey);
        $updateSecureKey->execute(array(":user_id"=>$this->userId));

        $queryGetSecureKey = "SELECT secure_key FROM users WHERE id = :user_id";
        $secureKey = $this->sqlDataBase->prepare($queryGetSecureKey);
        $secureKey->execute(array(":user_id"=>$this->userId));
        $secureKeyArr = $secureKey->fetch(PDO::FETCH_ASSOC);
        $this->secureKey = $secureKeyArr['secure_key'];
	}

    /**List all users by id and username on the application
     * @return array
     */
    public function GetAllUsers()
    {
       $queryAllUsers = "SELECT id, user_name FROM users ORDER BY user_name";
       $allUsers = $this->sqlDataBase->prepare($queryAllUsers);
       $allUsers->execute();
       $allUsersArr = $allUsers->fetchAll(PDO::FETCH_ASSOC);
       return $allUsersArr;
    }

    public function GetAllUsersFullInfo()
    {
        $queryAllUserInfo = "SELECT u.first, u.last, u.email, u.department_id, u.group_id, g.group_name, uc.cfop, d.department_name, CONCAT(u.last, ', ', u.first) as full_name
								FROM users u
									LEFT JOIN user_cfop uc ON (uc.user_id = u.id AND uc.default_cfop=1)
									LEFT JOIN groups g ON (g.id=u.group_id)
									LEFT JOIN departments d ON (d.id=u.department_id)";
        $allUserInfo = $this->sqlDataBase->prepare($queryAllUserInfo);
        $allUserInfo->execute();
        $allUserInfoArr = $allUserInfo->fetchAll(PDO::FETCH_ASSOC);

		for($i=0; $i<count($allUserInfoArr); $i++){
			$allUserInfoArr[$i]['cfop'] = UserCfop::formatCfop($allUserInfoArr[$i]['cfop']);
		}

        return $allUserInfoArr;
    }
    /**Get all users with a certain status
     * @param $statusId
     * @return array
     */
    public function GetUsers($statusId)
    {
        $queryAllUsers = "SELECT id, user_name FROM users WHERE status_id=:status_id ORDER BY user_name";
        $allUsers = $this->sqlDataBase->prepare($queryAllUsers);
        $allUsers->execute(array(":status_id"=>$statusId));
        $allUsersArr = $allUsers->fetchAll(PDO::FETCH_ASSOC);

        return $allUsersArr;
    }

    /**Get all users which are in a given group
     * @param $groupId
     * @return array
     */
    public function GetGroupUsers($groupId)
    {
        $queryGroupUsers = "SELECT * FROM users WHERE group_id=:group_id";
        $groupUsers = $this->sqlDataBase->prepare($queryGroupUsers);
        $groupUsers->execute(array(":group_id"=>$groupId));
        $groupUsersArr = $groupUsers->fetchAll(PDO::FETCH_ASSOC);

        return $groupUsersArr;
    }

    /**Get all users which are in a department
     * @param $departmentId
     * @return array
     */
    public function GetDepartmentUsers($departmentId)
    {
        $queryDepartmentUsers = "SELECT * FROM users WHERE department_id=:department_id";
        $departmentUsers = $this->sqlDataBase->prepare($queryDepartmentUsers);
        $departmentUsers->execute(array(":department_id"=>$departmentId));
        $departmentUsersArr = $departmentUsers->fetchAll(PDO::FETCH_ASSOC);

        return $departmentUsersArr;
    }

    /**Get all user roles
     * @return mixed
     */
    public function GetUserRoles()
    {
        $queryUserRoles = "SELECT * FROM user_roles";
        $userRoles = $this->sqlDataBase->prepare($queryUserRoles);
        $userRoles->execute();
        return $userRoles->fetchAll(PDO::FETCH_ASSOC);
    }

    public function GetUserStatusList()
    {
        $queryUserStatusList = "SELECT * FROM status WHERE type=:type";
        $userStatusList = $this->sqlDataBase->prepare($queryUserStatusList);
        $userStatusList->execute(array(':type'=>User::STATUS_TYPE_USER));
        $userStatusListArr = $userStatusList->fetchAll(PDO::FETCH_ASSOC);

        return $userStatusListArr;

    }

    public function AddCfop($cfop)
    {

        $this->userCfop->CreateUserCfop($this->userId, $cfop, "");
    }

    public function ListCfops()
    {

        return $this->userCfop->ListCfops($this->userId);
    }

    public function SetDefaultCfop($defaultCfopId)
    {
        $this->userCfop->LoadUserCfop($defaultCfopId);
        $this->userCfop->SetDefaultCfop();
    }
    //Getters and setters
    public function GetUserId()
	{
		return $this->userId;
	}

	public function GetUserName()
	{
		return $this->username;
	}

	public function SetUserName($username)
	{
		$this->username=$username;
	}

	public function GetFirst()
	{
		return $this->first;
	}

	public function SetFirst($first)
	{
		$this->first=$first;
	}

	public function GetLast()
	{
		return $this->last;
	}

	public function SetLast($last)
	{
		$this->last=$last;
	}

	public function GetEmail()
	{
		return $this->email;
	}

	public function SetEmail($email)
	{
		$this->email = $email;
	}

	public function GetDepartmentId()
	{
		return $this->departmentId;
	}
	
	public function SetDepartmentId($departmentId)
	{
		$this->departmentId = $departmentId;
	}

	public function GetGroupId()
	{
		return $this->groupId;
	}

	public function SetGroupId($groupId)
	{
		$this->groupId = $groupId;
	}

	public function GetRateId()
	{
		return $this->rateid;
	}

	public function SetRateId($rateId)
	{
		$this->rateid = $rateId;
	}
	
	public function GetStatusId()
	{
		return $this->statusid;
	}

	public function SetStatusId($statusid)
	{
		$this->statusid = $statusid;
	}
		
	public function GetUserRoleId()
	{
		return $this->userRoleId;
	}

	public function SetUserRoleId($usertypeid)
	{
		$this->userRoleId = $usertypeid;
	}

	public function GetDateAdded()
	{
		return $this->dateAdded;
	}	

	public function GetSecureKey()
	{
		return $this->secureKey;
	}
}
	
?>
