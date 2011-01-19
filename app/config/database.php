<?php

use row\database\adapter\MySQL;
use row\database\Model;

$db = new MySQL(array('user' => 'blog', 'dbname' => 'blog'));

Model::dbObject($db);


