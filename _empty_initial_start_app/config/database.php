<?php

use row\database\adapter\MySQL;
use row\database\Model;

$db = MySQL::open(array('user' => 'root', 'dbname' => 'test', 'names' => 'utf8'));

if ( !$db->connected() ) {
	exit('No db connection');
}

Model::dbObject($db);


