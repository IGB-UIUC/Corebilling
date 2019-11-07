<?php

class User
{
    private $db;

    const ACTIVE = 5, HIDDEN = 6, DISABLED = 7;
    const STATUS_TYPE_USER = 2;

    private $userId;
    private $username;
    private $first;
    private $last;
    private $email;
    private $departmentId;
    private $groupIds;
    private $rateid;
    private $statusid;
    private $userRoleId;
    private $dateAdded;
    private $secureKey;
    private $userCfop;
    private $certified;

    private $demographics = null;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->userId = 0;
        $this->username = "";
        $this->first = "";
        $this->last = "";
        $this->email = "";
        $this->departmentId = 0;
        $this->groupIds = [];
        $this->rateid = 9;
        $this->statusid = 7;
        $this->dateAdded = "";
        $this->secureKey = "";
        $this->userCfop = new UserCfop($this->db);
        $this->certified = 0;
        $this->userRoleId = 3;
    }

    public function __destruct() {

    }

    /**Create User
     * @param $username
     * @param $first
     * @param $last
     * @param $email
     * @param $departmentId
     * @param $rateId
     * @param $statusId
     * @param $userRoleId
     * @param $certified
     */
    public function create(
        $username,
        $first,
        $last,
        $email,
        $departmentId,
        $rateId,
        $statusId,
        $userRoleId,
        $certified
    ) {
        $this->username = $username;
        $this->first = $first;
        $this->last = $last;
        $this->email = $email;
        $this->departmentId = $departmentId;
        $this->rateid = $rateId;
        $this->statusid = $statusId;
        $this->userRoleId = $userRoleId;
        $this->dateAdded = date('Y-m-d H:i:s');
        $this->certified = $certified;
        if ( User::exists($this->db, $this->username) == 0 ) {
            $queryAddUser = "insert into users (user_name, first,last,email,department_id,rate_id,status_id,date_added,secure_key,user_role_id,certified)
								   values(:user_name,:first,:last,:email,:department_id,:rate_id,:status_id,NOW(), MD5(RAND()),:user_role_id,:certified)";
            $addUserPrepare = $this->db->prepare($queryAddUser);
            $addUserPrepare->execute(
                array(
                    ':user_name' => $this->username,
                    ':first' => $this->first,
                    ':last' => $this->last,
                    ':email' => $this->email,
                    ':department_id' => $this->departmentId,
                    ':rate_id' => $rateId,
                    ':status_id' => $statusId,
                    ':user_role_id' => $this->userRoleId,
                    ':certified' => $this->certified ? 1 : 0,
                ));
            $this->userId = $this->db->lastInsertId();
            log::log_message("Added user '$username'");
        }
    }

    /**Load user into this object
     * @param $id
     */
    public function load($id) {
        $queryUserInfo = "select * from users where id=:user_id";
        $userInfo = $this->db->prepare($queryUserInfo);
        $userInfo->execute(array(":user_id" => $id));
        $userInfoArr = $userInfo->fetch(PDO::FETCH_ASSOC);
        $this->userId = $userInfoArr["id"];
        $this->username = $userInfoArr["user_name"];
        $this->first = $userInfoArr["first"];
        $this->last = $userInfoArr["last"];
        $this->email = $userInfoArr["email"];
        $this->departmentId = $userInfoArr["department_id"];
        $this->rateid = $userInfoArr["rate_id"];
        $this->statusid = $userInfoArr["status_id"];
        $this->userRoleId = $userInfoArr["user_role_id"];
        $this->dateAdded = $userInfoArr["date_added"];
        $this->secureKey = $userInfoArr['secure_key'];
        $this->certified = $userInfoArr['certified'];
    }

    /**
     * Update user into database based on changes made to this object
     */
    // TODO the db should be updated on *every* set function, not just when this update function is called.
    public function update() {
        $queryUpdateUser = "update users set
							user_name=:user_name,
							first=:first,
							last=:last,
							email=:email,
							department_id=:department_id,
							rate_id=:rate_id,
							status_id=:status_id,
							user_role_id=:user_role_id,
							certified=:certified
							where id=:user_id";
        $updateUserPrep = $this->db->prepare($queryUpdateUser);
        return $updateUserPrep->execute(
            array(
                ':user_name' => $this->username,
                ':first' => $this->first,
                ':last' => $this->last,
                ':email' => $this->email,
                ':department_id' => $this->departmentId,
                ':rate_id' => $this->rateid,
                ':status_id' => $this->statusid,
                ':user_role_id' => $this->userRoleId,
                ':certified' => $this->certified ? 1 : 0,
                ':user_id' => $this->userId,
            ));
    }

    /**Check if a user exists by netid
     * @param PDO $db
     * @param     $username
     * @return int
     */
    public static function exists($db, $username) {
        $queryUserName = "select id from users where user_name = :user_name";
        $userName = $db->prepare($queryUserName);
        $userName->execute(array(":user_name" => $username));
        $userNameArr = $userName->fetch(PDO::FETCH_ASSOC);

        if ( $userName->rowCount() > 0 ) {
            return $userNameArr["id"];
        } else {
            return 0;
        }

    }

    public function hasAccessTo($deviceId) {
        if ( $this->isAdmin() ) { // Admins can access everything
            return true;
        } else {
            $query = "select * from access_control where device_id=:resource_id and user_id=:user_id limit 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute(array(":resource_id" => $deviceId, ":user_id" => $this->getId()));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result !== false;
        }
    }

    public function giveAccessTo($deviceId) {
        $query = "insert into access_control (user_id, device_id) values (:userid,:deviceid)";
        $stmt = $this->db->prepare($query);
        if ( $stmt->execute(array(":userid" => $this->getId(), ":deviceid" => $deviceId)) ) {
            log::log_message("Gave user '" . $this->getUsername() . "' access to device $deviceId");
        }
    }

    public function removeAccessTo($deviceId) {
        $query = "delete from access_control where user_id=:userid and device_id=:deviceid limit 1";
        $stmt = $this->db->prepare($query);
        if ( $stmt->execute(array(":userid" => $this->getId(), ":deviceid" => $deviceId)) ) {
            log::log_message("Removed access to device $deviceId for user '" . $this->getUsername() . "'");
        }
    }

    /**
     * Update security key for user
     */
    public function updateSecureKey() {
        $queryUpdateSecureKey = "update users set secure_key=MD5(RAND()) where id = :user_id";
        $updateSecureKey = $this->db->prepare($queryUpdateSecureKey);
        $updateSecureKey->execute(array(":user_id" => $this->userId));

        $queryGetSecureKey = "select secure_key from users where id = :user_id";
        $secureKey = $this->db->prepare($queryGetSecureKey);
        $secureKey->execute(array(":user_id" => $this->userId));
        $secureKeyArr = $secureKey->fetch(PDO::FETCH_ASSOC);
        $this->secureKey = $secureKeyArr['secure_key'];
    }

    /**List all users by id and username on the application
     * @param PDO $db
     * @return array
     */
    public static function getAllUsers($db) {
        $queryAllUsers = "select id, user_name from users order by user_name";
        $allUsers = $db->prepare($queryAllUsers);
        $allUsers->execute();
        $allUsersArr = $allUsers->fetchAll(PDO::FETCH_ASSOC);
        return $allUsersArr;
    }

    /**List all active users by id and username on the application
     * @param PDO $db
     * @return array
     */
    public static function getAllActiveUsers($db) {
        $queryAllUsers = "select id, user_name from users where status_id=5 order by user_name";
        $allUsers = $db->prepare($queryAllUsers);
        $allUsers->execute();
        $allUsersArr = $allUsers->fetchAll(PDO::FETCH_ASSOC);
        return $allUsersArr;
    }

    /**
     * @param PDO $db
     * @return mixed
     */
    public static function getAllUsersFullInfo($db) {
        $queryAllUserInfo = "select u.first, 
       								u.last, 
								   	u.email, 
								   	u.department_id,
								   	GROUP_CONCAT(g.group_name separator ', ') as group_name, 
								   	uc.cfop, 
								   	d.department_name, 
								  	u.date_added, 
								   	(select max(`stop`) from `session` where user_id=u.`id`) as last_login, 
								   	CONCAT(u.last, ', ', u.first) as full_name, 
								   	s.statusname as status, 
								   	u.id, ud.edu_level, 
								   	ud.gender,
								   	ud.underrepresented 
								from users u 
								  left join user_cfop uc on (uc.user_id = u.id and uc.default_cfop=1) 
								  left join user_groups ug on (u.id=ug.user_id)
								  left join `groups` g on (g.id=ug.group_id) 
								  left join departments d on (d.id=u.department_id) 
								  left join status s on s.id=u.status_id 
								  left join user_demographics ud on u.id = ud.user_id
								  group by u.id";
        $allUserInfo = $db->prepare($queryAllUserInfo);
        $allUserInfo->execute();
        $allUserInfoArr = $allUserInfo->fetchAll(PDO::FETCH_ASSOC);

        for ( $i = 0; $i < count($allUserInfoArr); $i++ ) {
            $allUserInfoArr[$i]['cfop'] = UserCfop::formatCfop($allUserInfoArr[$i]['cfop']);
            $allUserInfoArr[$i]['edit'] = '<a href="edit_users.php?user_id=' . $allUserInfoArr[$i]['id'] . '">Edit</a>';
        }

        return $allUserInfoArr;
    }

    /**
     * @param PDO $db
     * @param     $startyear
     * @param     $startmonth
     * @param     $endyear
     * @param     $endmonth
     * @return mixed
     */
    public static function getActiveUsers($db, $startyear, $startmonth, $endyear, $endmonth) {
        $queryAllUserInfo = "select u.first, 
								   u.last, 
								   u.id, 
								   u.user_name, 
								   u.email, 
								   u.department_id, 
								   GROUP_CONCAT(distinct g.group_name separator ', ') as group_name, 
								   d.department_name, 
								   CONCAT(u.last, ', ', u.first) as full_name
								from users u 
								  left join user_groups ug on (u.id=ug.user_id)
								  left join `groups` g on (g.id=ug.group_id) 
								  left join departments d on d.id=u.department_id 
								  left join `session` s on s.user_id=u.id
								where u.`status_id`=5 
								  and ((MONTH(start)>=:startmonth and YEAR(start)=:startyear) or YEAR(start)>:startyear) 
								  and ((MONTH(start)<=:endmonth and YEAR(start)=:endyear) or YEAR(start)<:endyear)
								group by u.id";
        $allUserInfo = $db->prepare($queryAllUserInfo);
        $allUserInfo->execute(
            array(
                ':startyear' => $startyear,
                ':startmonth' => $startmonth,
                ':endyear' => $endyear,
                ':endmonth' => $endmonth,
            ));
        $allUserInfoArr = $allUserInfo->fetchAll(PDO::FETCH_ASSOC);

        return $allUserInfoArr;
    }

    /**Get all user roles
     * @param PDO $db
     * @return mixed
     */
    public static function getUserRoles($db) {
        $queryUserRoles = "select * from user_roles";
        $userRoles = $db->prepare($queryUserRoles);
        $userRoles->execute();
        return $userRoles->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param PDO $db
     * @return mixed
     */
    public static function getUserStatusList($db) {
        $queryUserStatusList = "select * from status where type=:type";
        $userStatusList = $db->prepare($queryUserStatusList);
        $userStatusList->execute(array(':type' => User::STATUS_TYPE_USER));
        $userStatusListArr = $userStatusList->fetchAll(PDO::FETCH_ASSOC);

        return $userStatusListArr;

    }

    public function addCFOP($cfop) {
        $this->userCfop->create($this->userId, $cfop, "");
    }

    public function getAllCFOPs() {
        return UserCfop::getAllCFOPs($this->db, $this->userId);
    }

    public function getDefaultCFOP() {
        $this->userCfop->loadDefaultCfop($this->userId);
        return $this->userCfop->getCfop();
    }

    public function setDefaultCFOP($defaultCfopId) {
        $this->userCfop->load($defaultCfopId);
        $this->userCfop->setAsDefaultCFOP();
    }

    public function isAdmin() {
        return $this->getRoleId() == 1;
    }

    public function isSupervisor() {
        return $this->getRoleId() == 2;
    }

    public function getDemographics() {
        if ( $this->demographics === null ) {
            $this->demographics = new UserDemographics($this->db, $this->getId());
        }
        return $this->demographics;
    }

    //Getters and setters
    public function getId() {
        return $this->userId;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        if ( $this->username != $username ) {
            log::log_message("Set username of user '" . $this->username . "' to '$username'");
            $this->username = $username;
        }
    }

    public function getFirstName() {
        return $this->first;
    }

    public function setFirstName($first) {
        if ( $this->first != $first ) {
            $this->first = $first;
            log::log_message("Set first name of user '" . $this->username . "' to '$first'");
        }
    }

    public function getLastName() {
        return $this->last;
    }

    public function setLastName($last) {
        if ( $this->last != $last ) {
            $this->last = $last;
            log::log_message("Set last name of user '" . $this->username . "' to '$last'");
        }
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        if ( $this->email != $email ) {
            $this->email = $email;
            log::log_message("Set email of user '" . $this->username . "' to '$email'");
        }
    }

    public function getDepartmentId() {
        return $this->departmentId;
    }

    public function setDepartmentId($departmentId) {
        if ( $this->departmentId != $departmentId ) {
            $this->departmentId = $departmentId;
            log::log_message("Set department of user '" . $this->username . "' to '$departmentId'");
        }
    }

    public function getGroupIds() {
        $sql = "select group_id from user_groups where user_id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $this->getId()]);

        $ids = [];
        while ( $row = $stmt->fetch() ) {
            $ids[] = $row['group_id'];
        }

        return $ids;
    }

    public function setGroupIds($ids) {
    	/** @var LdapManager $ldapman */
    	global $ldapman;
        $addStmt = $this->db->prepare('insert into user_groups (user_id, group_id) values (:user, :group)');
        $deleteStmt = $this->db->prepare('delete from user_groups where group_id=:group and user_id=:user limit 1');

        $currentIds = $this->getGroupIds();
        foreach ( $ids as $id ) {
            if ( !in_array($id, $currentIds) ) {
                // not in group; add to group
				$group = new Group($this->db);
				$group->load($id);
                $addStmt->execute([':user' => $this->getId(), ':group' => $id]);
                log::log_message("Added user '" . $this->getUsername() . "' to group '" . $group->getName() . "'");
                if(LDAPMAN_API_ENABLED){
                	if($group->getNetid() != null) {
						$ldapman->addGroupMember(LDAPMAN_PI_PREFIX . $group->getNetid(), $this->getUsername());
					}
				}
            }
        }
        foreach ( $currentIds as $oldId ) {
            if ( !in_array($oldId, $ids) ) {
                // removed from group
                $deleteStmt->execute([':user' => $this->getId(), ':group' => $oldId]);
                log::log_message("Removed user '" . $this->getUsername() . "' from group '" . $oldId . "'");
				if(LDAPMAN_API_ENABLED){
					if($group->getNetid() != null) {
						$ldapman->removeGroupMember(LDAPMAN_PI_PREFIX . $group->getNetid(), $this->getUsername());
					}
				}
            }
        }
    }

    public function getRateId() {
        return $this->rateid;
    }

    public function setRateId($rateId) {
        if ( $this->rateid != $rateId ) {
            $this->rateid = $rateId;
            log::log_message("Set rate of user '" . $this->username . "' to '$rateId'");
        }
    }

    public function getStatusId() {
        return $this->statusid;
    }

    public function setStatusId($statusid) {
        if ( $this->statusid != $statusid ) {
            $this->statusid = $statusid;
            log::log_message("Set status of user '" . $this->username . "' to '$statusid'");
        }
    }

    public function getRoleId() {
        return $this->userRoleId;
    }

    public function setRoleId($usertypeid) {
        if ( $this->userRoleId != $usertypeid ) {
            $this->userRoleId = $usertypeid;
            log::log_message("Set role of user '" . $this->username . "' to '$usertypeid'");
        }
    }

    public function getDateAdded() {
        return $this->dateAdded;
    }

    public function getLastLogin() {
        $query = "select max(`stop`) as last_login from `session` where user_id=?";
        $stmt = $this->db->prepare($query);
        $stmt->execute(array($this->userId));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['last_login'];
    }

    public function getSecureKey() {
        return $this->secureKey;
    }

    public function isCertified() {
        return $this->certified;
    }

    public function setCertified($certified) {
        if ( $this->certified != $certified ) {
            $this->certified = $certified;
            if ( $certified ) {
                log::log_message("Certified user '" . $this->username . "'");
            } else {
                log::log_message("Un-certified user '" . $this->username . "'");
            }
        }
    }
}

?>
