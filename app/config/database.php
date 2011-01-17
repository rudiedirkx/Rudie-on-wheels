<?php

use row\database\adapter\MySQL;
use row\database\Model;

$db = MySQL::open(array('user' => 'blog', 'dbname' => 'blog'));

Model::dbObject($db);


