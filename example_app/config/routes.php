<?php

use row\http\Router;
use row\http\Route;

$router = new Router;



// Define a fallback controller (any type)
$router->add('/fallback/', array('controller' => 'app\\controllers\\fallbax'));

// To an applet
$router->add('/scaffolding', array('controller' => 'row\\applets\\scaffolding\\Controller'));

// A module alias
$router->add('/posts', array('controller' => 'app\\controllers\\blogController'));

// (1) This is how simple it **can** be
$router->add('/$', 'todo', array('redirect' => true));

// (2) Or somewhat more advanced. Notice the reverse arguments: %2 .. %1
$router->add('/record-id/(\d+)/of-table/([^/]+)$', '/dbsecrets/table-data/%2/pk/%1');

// (3) This should be possible (and do the exact same as (2)) because it's (much?) more efficient:
$router->add('/record-id/(\d+)/of-table/([^/]+)$', function($match) {
	return array(
		'controller' => 'dbsecrets',
		'action' => 'table_record',
		'arguments' => array($match[2], $match[1]),
	);
});

// (4) Also should-be possible because just too easy: (blogController is of type "generic")
$router->add('/blog/view/best$', array('controller' => 'blog', 'action' => 'best', 'arguments' => array(6)));
// With a Controller of type "specific" this is obviously easier without Route

// (5) Almost as easy, but with 1 (auto-)argument
$router->add('/blog/(\d+)$', array('controller' => 'blog', 'action' => 'view'));


