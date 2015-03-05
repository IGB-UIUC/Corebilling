<?php

function __autoload($class_name) {
	if(file_exists('classes/' . $class_name . '.php'))
	{
    		include 'classes/' . $class_name . '.php';
	}
}
?>
