<?php
include('includes/initializer.php');

$pages = new Pages($sqlDataBase);
$pagelist = $pages->GetPagesList();
$default = $pages->GetDefaultPage();

header('location:'.$pagelist[$default]['file']);