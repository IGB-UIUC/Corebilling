<?php
	class LdapManager {
		var $apiUrl;
		var $username;
		var $password;
		
		var $code;
		var $msg;
		
		function __construct($apiUrl,$username="",$password=""){
			$this->apiUrl = $apiUrl;
			$this->username = $username;
			$this->password = $password;
		}
		
		private function queryPOST($data){
			$options = array(
				'http' => array(
					'header' => "Content-type: application/x-www-form-urlencoded\r\n",
					'method' => 'POST',
					'content' => http_build_query($data)
				)
			);
			$context = stream_context_create($options);
			$result = file_get_contents($this->apiUrl, false, $context);
			if($result === FALSE){
				$this->code = 500;
				$this->msg = 'Error completing POST query';
				return json_decode("{code:500,msg:'Error completing POST query'}");
			} else {
				$result = json_decode($result);
				$this->code = $result->code;
				$this->msg = $result->msg;
				return $result;
			}
		}
		
		function getGroupMembers($gid){
			$data = array(
				'task'=>'group_members',
				'gid'=>'cnrgwiki'	
			);
			$result = self::queryPOST($data);
			if($result->code == 200){
				return $result->members;
			} else {
				return array();
			}
		}
		
		function addGroupMember($gid,$uid){
			$data = array(
				'task'=>'add_to_group',
				'username'=>$this->username,
				'password'=>$this->password,
				'gid'=>$gid,
				'uid'=>$uid
			);
			$result = self::queryPOST($data);
			if($result->code == 200){
				return true;
			} else {
				return false;
			}
		}
		
		function removeGroupMember($gid,$uid){
			$data = array(
				'task'=>'remove_from_group',
				'username'=>$this->username,
				'password'=>$this->password,
				'gid'=>$gid,
				'uid'=>$uid
			);
			$result = self::queryPOST($data);
			if($result->code == 200){
				return true;
			} else {
				return false;
			}
		}
	}
