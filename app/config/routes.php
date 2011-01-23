<?php

use row\http\Router;
use row\http\Route;

$router = new Router;

$router->add('/', '/todo', array('redirect' => true));

$router->add('/todo/(\d+)', '/todo/issue/%d');

$router->add('/gimme/some/blog', array('controller' => 'blog', 'action' => 'best', 'arguments' => array(10)));


