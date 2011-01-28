<?php

use row\database\adapter\MySQL;
use row\database\Model;

$db = MySQL::open(array('user' => 'blog', 'dbname' => 'blog', 'names' => 'utf8')); // Probably a MySQLi instance (not MySQL)

if ( !$db->connected() ) {
	exit('I\'m brutally ending your request due to a fatal SQL connection error... Edit config/database.php to overcome this gruesomeness!');
}

//$db->throwExceptions = false; // If you wanna check FALSE returns

Model::dbObject($db);


