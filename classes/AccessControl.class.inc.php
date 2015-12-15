<?php

/**
 * Created by PhpStorm.
 * User: nevoband
 * Date: 5/12/14
 * Time: 10:01 AM
 */
class AccessControl
{

    //resource types
    const RESOURCE_DEVICE = 1, RESOURCE_PAGE = 2;
    //Premissions
    const PERM_ALLOW = 1, PERM_DISALLOW = 0, PERM_ADMIN = 2, PERM_SUPERVISOR=3;
    //Participant Type
    const PARTICIPANT_USER = 20, PARTICIPANT_GROUP = 10, PARTICIPANT_ROLE = 0;

    private $sqlDataBase;

    public function __construct(PDO $sqlDataBase)
    {
        $this->sqlDataBase = $sqlDataBase;
    }

    public function __destruct()
    {

    }

    /**
     * Checks whether a user id is authorized to use the give resource
     * Check against user's group, role, and user id
     * @param $userId
     * @param $resourceTypeId
     * @param $resourceId
     * @return mixed
     */
    public function GetPermissionLevel($userId, $resourceTypeId, $resourceId)
    {
        $user = new User($this->sqlDataBase);
        $user->LoadUser($userId);
        $user->GetUserRoles();

        $queryResourcePermission = "SELECT permission FROM access_control WHERE resource_id=:resource_id AND resource_type_id=:resource_type_id
                                    AND ( (participant_type_id=:type_role AND participant_id=:role_id) OR (participant_type_id=:type_user AND participant_id=:user_id) OR (participant_type_id=:type_group AND participant_id=:group_id))
                                    ORDER BY participant_type_id DESC LIMIT 1";
        $resourcePermission = $this->sqlDataBase->prepare($queryResourcePermission);
        $resourcePermission->execute(array(":resource_id" => $resourceId, ":resource_type_id" => $resourceTypeId, ":role_id" => $user->GetUserRoleId(),
            ":type_user" => AccessControl::PARTICIPANT_USER, ":type_group" => AccessControl::PARTICIPANT_GROUP, ":type_role" => AccessControl::PARTICIPANT_ROLE,
            ":user_id" => $user->GetUserId(), ":group_id" => $user->GetGroupId(), ":role_id" => $user->GetUserRoleId()));
        $resourcePermissionArr = $resourcePermission->fetch(PDO::FETCH_ASSOC);

        return $resourcePermissionArr['permission'];
    }

    /**
     * Create a new Role with the given name
     * By default this role is not allowed access to any resource
     * @param $roleName
     */
    public function CreateRole($roleName)
    {
        $queryAddRole = "INSERT INTO user_roles (role_name) VALUES(:role_name)";
        $addRole = $this->sqlDataBase->prepare($queryAddRole);
        $addRole->execute(array(":role_name" => $roleName));
    }

    /**Set access for a participant on a resource
     * @param $resourceTypeId
     * @param $resourceId
     * @param $participantTypeId
     * @param $participantId
     * @param $permission
     */
    public function SetAccess($resourceTypeId, $resourceId, $participantTypeId, $participantId, $permission)
    {
        $accessInfo = $this->AccessExists($resourceTypeId, $resourceId, $participantTypeId, $participantId);
        if($accessInfo['id'])
        {
            //If permissions is disallowed then delete the access row. By default if access does not exist then it is disallowed
            //Only do this if the participant type is a Role.
            if($permission==AccessControl::PERM_DISALLOW && $participantTypeId == AccessControl::PARTICIPANT_ROLE)
            {
                $this->DeleteAccess($accessInfo['id']);
            }
            else
            {
                //If access id already exists then update the permissions for it
                if($accessInfo['permission']==$permission)
                {
                    //Do nothing, permission is the same
                }
                else
                {
                    //Permission has changed so do an update
                    $this->UpdateAccess($accessInfo['id'],$permission);
                }
            }
        }
        else
        {
            //If the participant type is not role then we do want to add a disallow permission to the access control table
            //This allows us to overwrite the Role permissions for a User or a Group
            if($permission == AccessControl::PERM_DISALLOW && $participantTypeId == AccessControl::PARTICIPANT_ROLE)
            {
                //If access id does not exist and permission is disallowed then do nothing
            }
            else
            {
                //If access does not exist then add permission for it.
                $this->AddAccess($resourceTypeId, $resourceId, $participantTypeId, $participantId, $permission);
            }
        }
    }

    /**Add access for a specific resource specified by it's resource type and resource id
     * role=0 to allow access to specific group
     * @param $resourceTypeId
     * @param $resourceId
     * @param $participantTypeId
     * @param $participantId
     * @param $permission
     */
    private function AddAccess($resourceTypeId, $resourceId, $participantTypeId, $participantId, $permission)
    {
            $queryAddAccess = "INSERT INTO access_control (resource_type_id, resource_id, permission, participant_type_id, participant_id)
                                VALUES(:resource_type_id,:resource_id, :permission, :participant_type_id, :participant_id)";
            $addAccess = $this->sqlDataBase->prepare($queryAddAccess);
            $addAccess->execute(array(":resource_type_id" => $resourceTypeId,
                ":resource_id" => $resourceId, ":permission" => $permission,
                ":participant_type_id" => $participantTypeId, ":participant_id" => $participantId));
    }

    /**Get a list of all roles available
     * @return mixed
     */
    public function GetRolesList()
    {
        $queryUserRoles = "SELECT * FROM user_roles";
        $userRoles = $this->sqlDataBase->prepare($queryUserRoles);
        $userRoles->execute();
        $userRolesArr = $userRoles->fetch(PDO::FETCH_ASSOC);

        return $userRolesArr;
    }

    /**List participants
     * @param $participantTypeId
     * @return PDOStatement
     */
    public function GetParticipantsList($participantTypeId)
    {
        $queryParticipantList = "";
        switch ($participantTypeId) {
            case AccessControl::PARTICIPANT_ROLE:
                $queryParticipantList = "SELECT id as participant_id, role_name as participant_name FROM user_roles";
                break;
            case AccessControl::PARTICIPANT_GROUP:
                $queryParticipantList = "SELECT id as participant_id, group_name as participant_name  FROM groups";
                break;
            case AccessControl::PARTICIPANT_USER:
                $queryParticipantList = "SELECT id as participant_id, user_name as participant_name  FROM users";
                break;
        }
        return $this->sqlDataBase->query($queryParticipantList);

    }

    /**Update access for an access id
     * @param $accessId
     * @param $permission
     */
    private function UpdateAccess($accessId, $permission)
    {
        $queryUpdateAccess = "UPDATE access_control SET permission=:permission WHERE id=:access_id";
        $updateAccess = $this->sqlDataBase->prepare($queryUpdateAccess);
        $updateAccess->execute(array(":access_id" => $accessId, ":permission" => $permission));
    }

    /**Check if the permission exists already
     * return access_control id if it exists otherwise return 0
     * @param $resourceTypeId
     * @param $resourceId
     * @param $participantTypeId
     * @param $participantId
     * @return int
     */
    public function AccessExists($resourceTypeId, $resourceId, $participantTypeId, $participantId)
    {
        $queryAccessExists = "SELECT id, permission FROM access_control WHERE participant_type_id=:participant_type_id AND participant_id=:participant_id AND resource_type_id=:resource_type_id AND resource_id=:resource_id";
        $accessExists = $this->sqlDataBase->prepare($queryAccessExists);
        $accessExists->execute(array(":participant_type_id" => $participantTypeId,":participant_id"=>$participantId,":resource_type_id"=>$resourceTypeId, ":resource_id"=>$resourceId));
        $accessExistsArr = $accessExists->fetch(PDO::FETCH_ASSOC);
        if($accessExistsArr['id'])
        {
            return $accessExistsArr;
        }

        return 0;
    }

    /**Delete an access permission by access id
     * @param $accessId
     */
    public function DeleteAccess($accessId)
    {
        $queryDeleteAccess = "DELETE FROM access_control WHERE id=:access_id";
        $deleteAccess = $this->sqlDataBase->prepare($queryDeleteAccess);
        $deleteAccess->execute(array(":access_id" => $accessId));
    }

    /**Get a list of access permissions given a resource type and participant type
     * @param $resourceTypeId
     * @param $participantTypeId
     * @param $participantId
     * @return mixed
     */
    public function GetAccessList($resourceTypeId, $participantTypeId, $participantId)
    {
        switch ($resourceTypeId) {
            case AccessControl::RESOURCE_DEVICE:
                $queryAccessList = "SELECT CASE WHEN ac.id IS NULL THEN 0 ELSE ac.id END as id, CASE WHEN ac.permission IS NULL THEN 0 ELSE ac.permission END as permission, d.full_device_name as resource_name, d.id as resource_id FROM device d LEFT JOIN access_control ac ON  ac.resource_id=d.id
                                    AND (ac.resource_type_id=:resource_type_id) AND (ac.participant_type_id=:participant_type_id)
                                    AND (ac.participant_id=:participant_id) order by d.full_device_name";
                break;
            case AccessControl::RESOURCE_PAGE:
                $queryAccessList = "SELECT CASE WHEN ac.id IS NULL THEN 0 ELSE ac.id END as id, CASE WHEN ac.permission IS NULL THEN 0 ELSE ac.permission END as permission, p.page_name as resource_name, p.id as resource_id FROM pages p LEFT JOIN access_control ac ON  ac.resource_id=p.id
                                    AND (ac.resource_type_id=:resource_type_id) AND (ac.participant_type_id=:participant_type_id)
                                    AND (ac.participant_id=:participant_id) order by p.id";
        }
        $accessList = $this->sqlDataBase->prepare($queryAccessList);
        $accessList->execute(array(":resource_type_id" => $resourceTypeId, ":participant_type_id" => $participantTypeId, ":participant_id" => $participantId));
        /*
        $queryAccessListPrint = str_replace(":resource_type_id",$resourceTypeId,$queryAccessList);
        $queryAccessListPrint = str_replace(":participant_type_id", $participantTypeId,$queryAccessListPrint);
        $queryAccessListPrint = str_replace(":participant_id", $participantId,$queryAccessListPrint);
        echo $queryAccessListPrint;
        */
        $accessListArr = $accessList->fetchAll(PDO::FETCH_ASSOC);
        return $accessListArr;
    }
}