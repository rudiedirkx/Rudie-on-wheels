<?php

use row\database\adapter\MySQL;
use row\database\Model;

$db = MySQL::open(array('localhost', 'blog', '', 'blog'));

Model::dbObject($db);


