<?php

require(ROW_VENDOR_ROW_PATH.'/database/Adapter.php');
require(ROW_VENDOR_ROW_PATH.'/database/adapter/MySQL.php');
require(ROW_VENDOR_ROW_PATH.'/database/adapter/MySQLi.php');

use row\database\adapter;
use row\database\Model;

//$db = adapter\MySQL::open(array('user' => DB_USER, 'pass' => DB_PASS, 'dbname' => DB_NAME, 'names' => 'utf8')); // typeof MySQLi (probably)
$db = adapter\SQLite::open(array('path' => DB_PATH));

if ( !$db->connected() ) {
	exit('I could not connect to the specified database. Edit <u>app/config/database.php</u> and import <u>app/_project/blog.sql</u> to fix this problem!');
}

//$db->throwExceptions = false; // Will return FALSE instead of throwing exceptions

Model::dbObject($db);


