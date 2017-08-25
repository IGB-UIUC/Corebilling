<?php
	
class Statistics 
{
	private $db;
	public function __construct($db){
		$this->db = $db;
	}
	
	public function get_reservation_usage($startyear,$startmonth,$endyear,$endmonth){
		$userQuery = "SELECT u.user_name, u.id from users u left join reservation_info r on r.user_id=u.id where u.`status_id`=5 and ((MONTH(r.start)>=:startmonth AND YEAR(r.start)=:startyear) OR YEAR(r.start)>:startyear) AND ((MONTH(r.start)<=:endmonth AND YEAR(r.start)=:endyear) OR YEAR(r.start)<:endyear) group by u.id";
		$userStmt = $this->db->prepare($userQuery);
		$userStmt->execute(array(':startyear'=>$startyear,':startmonth'=>$startmonth,':endyear'=>$endyear,':endmonth'=>$endmonth));
		$userArr = $userStmt->fetchAll();
		
		$resTimeQuery = "select sum(timestampdiff(SECOND,r.start,r.stop)) as res_time from reservation_info r where r.user_id=:userid and ((MONTH(r.start)>=:startmonth AND YEAR(r.start)=:startyear) OR YEAR(r.start)>:startyear) AND ((MONTH(r.start)<=:endmonth AND YEAR(r.start)=:endyear) OR YEAR(r.start)<:endyear)";
		$resTimeStmt = $this->db->prepare($resTimeQuery);
		
		$usedTimeQuery = "select sum(timestampdiff(SECOND,s.start,s.stop)) as used_time from `session` s where s.user_id=:userid and ((MONTH(s.start)>=:startmonth AND YEAR(s.start)=:startyear) OR YEAR(s.start)>:startyear) AND ((MONTH(s.start)<=:endmonth AND YEAR(s.start)=:endyear) OR YEAR(s.start)<:endyear)";
		$usedTimeStmt = $this->db->prepare($usedTimeQuery);
		
		$missedResQuery = "select count(id) as missed_res from reservation_info where user_id=:userid and id not in (select r.id from reservation_info r inner join `session` s on s.start <= r.stop and s.stop >= r.start where r.user_id=:userid and ((MONTH(r.start)>=:startmonth AND YEAR(r.start)=:startyear) OR YEAR(r.start)>:startyear) AND ((MONTH(r.start)<=:endmonth AND YEAR(r.start)=:endyear) OR YEAR(r.start)<:endyear) and r.device_id=s.device_id and r.user_id=s.user_id) and ((MONTH(start)>=:startmonth AND YEAR(start)=:startyear) OR YEAR(start)>:startyear) AND ((MONTH(start)<=:endmonth AND YEAR(start)=:endyear) OR YEAR(start)<:endyear) and `stop`<NOW() and deleted=0";
		$missedResStmt = $this->db->prepare($missedResQuery);
		
		$deletedResQuery = "select count(id) as del_res from reservation_info where user_id=:userid and deleted=1 and ((MONTH(start)>=:startmonth AND YEAR(start)=:startyear) OR YEAR(start)>:startyear) AND ((MONTH(start)<=:endmonth AND YEAR(start)=:endyear) OR YEAR(start)<:endyear)";
		$deletedResStmt = $this->db->prepare($deletedResQuery);
		
		$statsArr = array();
		for($i=0; $i<count($userArr); $i++){
			$resTimeStmt->execute(array(':userid'=>$userArr[$i]['id'],':startyear'=>$startyear,':startmonth'=>$startmonth,':endyear'=>$endyear,':endmonth'=>$endmonth));
			$resTimeArr = $resTimeStmt->fetch();
			
			$usedTimeStmt->execute(array(':userid'=>$userArr[$i]['id'],':startyear'=>$startyear,':startmonth'=>$startmonth,':endyear'=>$endyear,':endmonth'=>$endmonth));
			$usedTimeArr = $usedTimeStmt->fetch(); 
			
			$missedResStmt->execute(array(':userid'=>$userArr[$i]['id'],':startyear'=>$startyear,':startmonth'=>$startmonth,':endyear'=>$endyear,':endmonth'=>$endmonth));
			$missedResArr = $missedResStmt->fetch();
			
			$deletedResStmt->execute(array(':userid'=>$userArr[$i]['id'],':startyear'=>$startyear,':startmonth'=>$startmonth,':endyear'=>$endyear,':endmonth'=>$endmonth));
			$deletedResArr = $deletedResStmt->fetch();
			
			$statsArr[$i] = array('user_name'=>$userArr[$i]['user_name'], 'res_time'=>$resTimeArr['res_time']/3600, 'used_time'=>$usedTimeArr['used_time']/3600, 'used_ratio'=>$usedTimeArr['used_time']/$resTimeArr['res_time'], 'missed_res'=>$missedResArr['missed_res'], 'deleted_res'=>$deletedResArr['del_res']);
		}
		
		return $statsArr;
	}
}