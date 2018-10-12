<?php

// Log functions
class log {

	// Records the given message in the log file. $quiet logs in log file but not to screen
	public static function log_message($message) {
		global $login_user;
		if(isset($login_user)){
			$user_str = $login_user->getUsername();
		} else {
			$user_str = "guest";
		}
        $current_time = date('Y-m-d H:i:s');
        $full_msg = $current_time . " $user_str: " . $message . "\n";
        if (__ENABLE_LOG__) {
	        $fh = fopen(self::get_log_file(),'a');
	        fwrite($fh,$full_msg);
			fclose($fh);
        }
    }

	// Makes sure the log file exists and return its location
    public static function get_log_file() {
        if (!file_exists(__LOG_FILE__)) {
            touch(__LOG_FILE__);
        }
        return __LOG_FILE__;
    }
}

?>
