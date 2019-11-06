<?php
class Group
{
    private $db;
    
    private $groupId;
    private $groupName;
    private $description;
    private $departmentId;

	public function __construct(PDO $db)
	{
		$this->db = $db;
        $this->groupName="New Group";
	}
	
	public function __destruct()
	{
		
	}

    /**Add a group to the database and load it in the current object
     * @param $groupName
     * @param $description
     * @param $departmentId
     */
    public function create($groupName, $description, $departmentId)
	{
		$queryAddGroup = "INSERT INTO groups (group_name, description, department_id)VALUES(:group_name,:description,:department_id)";
        $addGroupPrep = $this->db->prepare($queryAddGroup);
        $addGroupPrep->execute(array(':group_name'=>$groupName,':description'=>$description,':department_id'=>$departmentId));
        $groupId = $this->db->lastInsertId();
        $this->groupName = $groupName;
        $this->description = $description;
        $this->departmentId = $departmentId;
        $this->groupId = $groupId;
        log::log_message("Added group '$groupName'");
	}

    /**Load a group into object from database given a group ID
     * @param $groupId
     */
    public function load($groupId)
	{
		$queryGroupInfo = "SELECT * FROM groups WHERE id=:id";
        $groupInfo = $this->db->prepare($queryGroupInfo);
        $groupInfo->execute(array(':id'=>$groupId));
        $groupInfoArr = $groupInfo->fetch(PDO::FETCH_ASSOC);
        $this->groupName = $groupInfoArr['group_name'];
        $this->description = $groupInfoArr['description'];
        $this->departmentId = $groupInfoArr['department_id'];
        $this->groupId = $groupId;
	}

    /**
     * Update group parameters in database
     */
    public function update()
    {
        $queryUpdateGroup = "UPDATE groups SET
                                group_name=:group_name,
                                description=:description,
                                department_id=:department_id
                                WHERE id=:group_id";

        $updateGroup = $this->db->prepare($queryUpdateGroup);
        $updateGroup->execute(array(":group_name"=>$this->groupName,":description"=>$this->description,":department_id"=>$this->departmentId,":group_id"=>$this->groupId));
    }

    /**Get a list of all groups by id and group_name
     * @return array
     */
    public static function getAllGroups($db)
	{
		$queryGroupList = "SELECT id, group_name FROM groups ORDER BY group_name";
        $groupList = $db->query($queryGroupList);
        $groupListArr = $groupList->fetchAll(PDO::FETCH_ASSOC);

        return $groupListArr;
	}

    /**Check if a group exists by groupName
     * @param $groupName
     * @return bool
     */
    public static function exists($db, $groupName)
    {
        $queryGroup = "SELECT COUNT(*) FROM groups WHERE group_name=:group_name";
        $group = $db->prepare($queryGroup);
        $group->execute(array(':group_name'=>$groupName));
        $groupCount = $group->fetchColumn();

        if($groupCount)
        {
            return true;
        }
        return false;
    }

    /**Get a list of all group members
     * @return array
     */
    public function getMembers()
    {
        if($this->getId())
        {
            $queryGroupUsers = "SELECT * FROM users u left join user_groups ug on u.id = ug.user_id WHERE ug.group_id=:group_id order by u.user_name";
			$groupUsers = $this->db->prepare($queryGroupUsers);
			$groupUsers->execute(array(":group_id"=>$this->getId()));
			$groupUsersArr = $groupUsers->fetchAll(PDO::FETCH_ASSOC);
            return $groupUsersArr;
        }
        return array();
    }


    //Getters and setters for this class

    /**
     * @param mixed $departmentId
     */
    public function setDepartmentId($departmentId)
    {
	    if($this->departmentId != $departmentId){
	        $this->departmentId = $departmentId;
	        log::log_message("Set department id for group '".$this->groupName."' to $departmentId");
	    }
    }

    /**
     * @return mixed
     */
    public function getDepartmentId()
    {
        return $this->departmentId;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
	    if($this->description != $description){
	        $this->description = $description;
	        log::log_message("Set description for group '".$this->groupName."' to '$description'");
	    }
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $groupId
     */
    public function setId($groupId)
    {
	    if($this->groupId != $groupId){
	        $this->groupId = $groupId;
	        log::log_message("Set id for group '".$this->groupName."' to $groupId");
	    }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->groupId;
    }

    /**
     * @param mixed $groupName
     */
    public function setName($groupName)
    {
	    if($this->groupName != $groupName){
	    	log::log_message("Set name for group '".$this->groupName."' to $groupName");
	        $this->groupName = $groupName;
	    }
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->groupName;
    }
}

?>