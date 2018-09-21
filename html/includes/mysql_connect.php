<?php

//@$dbc=mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die('Could not connect to MySQL:'.mysql_error());
//mysql_select_db(DB_NAME,$dbc) OR die ('could not select the database: '.mysql_error());

//Open SQL connection
//$db = new SQLDataBase(DB_HOST,DB_NAME, DB_USER, DB_PASSWORD);
try{
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER,DB_PASSWORD);
}catch(PDOException $e)
{
    echo $e->getMessage();
}

?>
