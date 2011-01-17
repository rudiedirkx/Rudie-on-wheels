<?php

use row\database\adapter\MySQL;
use row\database\Model;

$db = MySQL::open(array('localhost', 'rudie', 'rybinsk', 'blog'));

Model::dbObject($db);


