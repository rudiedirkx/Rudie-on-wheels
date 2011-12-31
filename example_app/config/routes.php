<?php

use app\specs\Router;

$router = new Router;

/**
 * Default route wildcards:
 *
	%	=>	[^/]+
	#	=>	\d+
	*	=>	.+
 *
 * These are extendable in option "action_path_wildcards" in app\specs\Dispatcher.
 *
 * Arguments of Router->add:
 *
	1) route		String					Matchable route with wildcards or actual regex
	2) destination	String/Array/Closure	Destination. Must output Location Array or URI
	3) options		Array					Eg. destination => 301
 *
 * A Location Array is an array with two mandatory elements:
	1) controller	String		Unfixed Controller name or full class name
	2) action		String		Unfixed Action name for that Controller
 * and one optional element:
	3) arguments	Array		List of arguments to pass to the Action
 *
 * The simplest routes are URI => URI, but the most efficient are URI => Location Array.
 *
 * The less Routes, the better! You can do almost anything with Controller
 * mapping (controllers.php) and Action mapping (in the Controller class).
 */

$router->add('/do/%/of/%/with/(.+)$', '%2/%1/%3');

$router->add('/do/%/of/%$', '%2/%1');

$router->add('/news/([a-z0-9\-]+)$', array(
	'controller' => 'app\controllers\blogController',
	'action' => 'viewByTitle'
));

$router->add('/posts(.*)$', 'blog%1');

$router->add('/blog/user/(\d+)', array(
	'controller' => 'app\controllers\blog\userController',
	'action' => 'profile'
));

$router->add('/$', 'todo', array('redirect' => 301));

$router->add('/blog/view/best$', array(
	'controller' => 'blog',
	'action' => 'best',
	'arguments' => array(6)
));

$router->add('/%/#', function($match) {
	return array(
		'controller' => $match[1],
		'action' => 'view',
		'arguments' => array($match[2]),
	);
});


