<?php

class html {
	public static function error_message($msg,$title=""){
		$alert = '<div class="alert alert-danger">';
		if($title != ""){
			$alert .= '<h3 class="alert-heading">'.$title.'</h3>';
		}
		$alert .= $msg.'</div>';
		return $alert;
	}
	public static function success_message($msg, $title=""){
		$alert = '<div class="alert alert-success">';
		if($title != ""){
			$alert .= '<h3 class="alert-heading">'.$title.'</h3>';
		}
		$alert .= $msg.'</div>';
		return $alert;
	}
}