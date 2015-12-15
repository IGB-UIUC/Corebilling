<?php
set_include_path(get_include_path().":../classes:includes/PHPExcel_1.8.0/Classes");
function my_autoloader($class_name) {
	if(file_exists('../classes/' . $class_name . '.class.inc.php'))
	{
    		include $class_name . '.class.inc.php';
	}
}
spl_autoload_register('my_autoloader');
?>
