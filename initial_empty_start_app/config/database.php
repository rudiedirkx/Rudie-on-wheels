<?php

require(ROW_VENDOR_ROW_PATH.'/database/Adapter.php');
require(ROW_VENDOR_ROW_PATH.'/database/adapter/MySQL.php');
require(ROW_VENDOR_ROW_PATH.'/database/adapter/MySQLi.php');

use row\database\adapter;
use row\database\Model;

//$db = adapter\MySQL::open(array('user' => 'username', 'pass' => 'password', 'dbname' => 'database', 'names' => 'utf8'));
$db = adapter\SQLite::open(array('path' => ROW_APP_PATH.'/runtime/blog.sqlite3'));

if ( !$db->connected() ) {
	exit('I could not connect to the specified database. Edit <u>app/config/database.php</u> and import <u>app/_project/blog.sql</u> to fix this problem!');
}

//$db->throwExceptions = false; // Will return FALSE instead of throwing exceptions

Model::dbObject($db);


