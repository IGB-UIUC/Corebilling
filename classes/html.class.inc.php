<?php

class html {
	public static function error_message($msg,$title=""){
		$alert = '<div class="alert alert-danger">';
		if($title != ""){
			$alert .= '<h3>'.$title.'</h3>';
		}
		$alert .= $msg.'</div>';
		return $alert;
	}
}