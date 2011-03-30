<?php

use row\database\adapter\MySQL;
use row\database\Model;

require(ROW_VENDOR_ROW_PATH.'/database/Adapter.php');
require(ROW_VENDOR_ROW_PATH.'/database/adapter/MySQL.php');
require(ROW_VENDOR_ROW_PATH.'/database/adapter/MySQLi.php');

$db = MySQL::open(array('user' => 'blog', 'dbname' => 'blog', 'names' => 'utf8')); // typeof MySQLi (probably)

if ( !$db->connected() ) {
	exit('I could not connect to the specified database. Edit <u>app/config/database.php</u> and import <u>app/_project/blog.sql</u> to fix this problem!');
}

//$db->throwExceptions = false; // Will return FALSE instead of throwing exceptions

Model::dbObject($db);


