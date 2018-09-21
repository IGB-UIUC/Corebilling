<?php
$userTypeAdmin = 1;

//set debug to 1 to print the e-mails instead of sending them
$debug = 0;

//Password used to make sure no random user can run this script
$password = "dlr24051";

//Setting up the includes files
include("includes/config.php");
include("classes/AutoLoadClasses.php");
include("includes/mysql_connect.php");

//Authorized to run this script
if(isset($_GET['pass']))
{
        $passGiven = $_GET['pass'];
}
else
{
        $passGiven = "";
}

if($passGiven==$password)
{
//Check for unused reservations and warn user and admins.

// Get all events that were not used and e-mail the users responsible
$queryUnusedReservations = "SELECT u.first,u.last,u.email,u.user_name, ei.start,ei.stop,ei.description,d.full_device_name FROM reservation_info ei, users u, device d WHERE
                                Date(ei.start) = (CURRENT_date() - INTERVAL 1 DAY)
                                AND u.id=ei.user_id
                                AND u.usertypeid!=".$userTypeAdmin."
                                AND d.id=ei.device_id
                                AND d.lasttick > '0000-00-00 00:00:00'
				AND d.id!=24
				AND d.id!=37
                                AND NOT EXISTS
                                (
                                        SELECT * FROM session s, users us WHERE (ei.user_id=s.user_id || us.usertypeid=".$userTypeAdmin.")
                                        AND Date(s.start) = (CURRENT_date() - INTERVAL 1 DAY)
                                        AND us.id = s.user_id
                                        AND (
                                                (s.start >= ei.start AND s.start <= ei.stop)
                                                OR (s.stop >= ei.start AND s.stop <= ei.stop)
                                                OR (s.start <= ei.start AND s.stop >= ei.stop)
                                        )
                                )
				";
$unusedReservations = $db->query($queryUnusedReservations);

if(isset($unusedReservations))
{
	foreach($unusedReservations as $id=>$unusedReservation)
	{
		$userTypeAdmin = 1;

		if(isset($unusedReservation))
	      	{
			$message = "Notice: Unused reserved time.\r\n\r\n";
                	$subject = "Unused reserved time";
	
        		$queryUserAdmins = "SELECT email,id FROM users WHERE usertypeid=".$userTypeAdmin;
              		$adminUsers = $db->query($queryUserAdmins);
             		//Email the user the event blongs too
             		$to = $unusedReservation['email'];
		        $headers = 'From: core-training@igb.illinois.edu' . "\r\n" .
       	            		'Reply-To: core-training@igb.illinois.edu' . "\r\n" .
        	                'X-Mailer: PHP/' . phpversion();
        		$subject .= " for ".$unusedReservation['user_name'];
		
       	        	$message .= "Reservation Information:\r\n\r\nUser Name: ".$unusedReservation['first']." ".$unusedReservation['last'].
        	                 	"\r\nDevice Name: ".$unusedReservation['full_device_name'].
        	                   	"\r\nStart: ".$unusedReservation['start'].
	               	              	"\r\nEnd:".$unusedReservation['stop'].
	               	             	"\r\nDescription:".$unusedReservation['description'].
                	            	"\r\n\r\nCurrently the LSM 710, LSM700, Real Time PCR and Imaris license are heavily used.  Users who fail to keep their appointments or cancel more than three appointments a month with less than 24 hours notice will be charged for future reserved time.".
                	           	"\r\n\r\nWe would appreciate it if everyone would be considerate of other people's time when using the calendars. ";
			if($debug)
			{
				echo $to."<br>";
				echo $subject."<br>";
				echo $message."<br>";
				echo "--------------------------------------------------<br><br>";
			}
			else
			{
	               		mail($to, $subject, $message, $headers);
			}
	
	            	//Email all administrators of this even happening
	            	foreach($adminUsers as $id=>$adminUser)
	             	{
	                   	$to      = $adminUser['email'];
	                    	$headers = 'From: core-training@igb.illinois.edu' . "\r\n" .
	                    	'Reply-To: core-training@igb.illinois.edu' . "\r\n" .
	                      	'X-Mailer: PHP/' . phpversion();

				if($debug)
				{
					echo $to."<br>";
                                	echo $subject."<br>";
                                	echo $message."<br>";
                                	echo "--------------------------------------------------<br><br>";
				}
				else
				{
	                     		mail($to, $subject, $message, $headers);
				}
	                }
	       	}
	}
}

//Remind users of upcoming reservations


}

?>
