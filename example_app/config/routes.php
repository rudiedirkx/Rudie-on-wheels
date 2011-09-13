<?php

use row\http\Router;

$router = new Router;


/**
 * Make as few routes as possible! It's much faster to make
 * internal routes with a specific Controller.
 * The more routes you add here, the work will be done EVERY
 * request no matter the Controller.
 * 
 * Advised routes are:
	- Crazy URLs (e.g. with reversed arguments)
	- Home: / (the only URI with no Controller)
 */


// To an applet
// $router->add('/scaffolding', array('controller' => 'row\\applets\\scaffolding\\Controller'));
// To an applet but with 'access control'
$router->add('/scaffolding', function() {
	if ( !in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1')) && hash('sha256', @$_GET['password']) !== 'cac74babf308c40b7f531013043631dff4f56434f3aab026f73feafe34564340' ) {
		exit('Access denied!');
	}
	return array('controller' => 'row\\applets\\scaffolding\\Controller');
});

// Blog post by slugged title
$router->add('/news/([a-z0-9\-]+)$', array(
	'controller' => 'app\\controllers\\blogController',
	'action' => 'viewByTitle'
));

// A module alias
$router->add('/posts', array(
	'controller' => 'app\\controllers\\blogController'
));

// JS cache
$router->add('/js/all.js$', array(
	'controller' => 'app\\controllers\\fallbax',
	'action' => 'allJS'
));

// Blog user profile
$router->add('/blog-user/(\d+)/?', array(
	'controller' => 'app\\controllers\\blog\\userController',
	'action' => 'profile'
));

// (1) This is how simple it **can** be
$router->add('/$', 'todo', array('redirect' => true));

// (2) Or somewhat more advanced. Notice the reverse arguments: %2 .. %1
// $router->add('/record-id/(\d+)/of-table/([^/]+)$', '/dbsecrets/table-data/%2/pk/%1');

// (3) This should be possible (and do the exact same as (2)) because it's (much?) more efficient:
$router->add('/record-id/(\d+)/of-table/([^/]+)$', function($match) {
	// You can even do some access control in here:
	if ( !in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1')) ) {
		exit('Access denied!');
	}
	// If you pass the 'access control', it's an easy route to the right Controller Action:
	return array(
		'controller' => 'row\\applets\\scaffolding\\Controller',
		'action' => 'table_record',
		'arguments' => array($match[2], $match[1]),
	);
});

// (4) Also should-be possible because just too easy: (blogController is of type "generic")
$router->add('/blog/view/best$', array('controller' => 'blog', 'action' => 'best', 'arguments' => array(6)));
// With a Controller of type "specific" this is obviously easier without Route

// (5) Almost as easy, but with 1 (auto-)argument
$router->add('/blog/(\d+)$', array('controller' => 'blog', 'action' => 'view'));


