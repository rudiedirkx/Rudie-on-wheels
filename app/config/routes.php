<?php

use row\http\Router;
use row\http\Route;

$router = new Router;

$router->add('/todo/(\d+)', '/todo/issue/%d');


