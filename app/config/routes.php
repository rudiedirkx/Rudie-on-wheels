<?php

use row\http\Router;
use row\http\Route;

$router = new Router;

// The 3rd argument is an Options object. Currently there's only 1 option: redirect =)
$router->add('/', '/todo', array('redirect' => true));

// If the 2nd argument is a string, it's sprintf'd to get the new requestPath.
// How this path is handled by the Controller (specific or generic) is still up to the controller.
$router->add('/todo/(\d+)', '/todo/issue/%d');

// If the 2nd argument is an (assoc) array, it's use for direct access to the controller and action.
// The simplest form:
$router->add('/gimme/some/blog', array(
	'controller' => 'blog',
	'action' => 'best',
	'arguments' => array(10),
));

// If you want, you can pass a function that will be called with the match results as (only) argument.
// In this case a function necessary, because the actionArguments are reversed:
$router->add('/something/big/(\d+)/with-something/small/(\d+)', function($match) {
	return array(
		'controller' => 'something',
		'action' => 'withBigAndSmallSpecified',
		'arguments' => array($match[2], $match[1]), // $match[0] is the complete matched string (so useless)
	);
});

// All match results are passed to the actionFunction as arguments.
// In this case only 1 of 4 'patterns' is an argument: [^/] is variable, but only (\d+) is a result
$router->add('/jobs/[^/]+/(\d+)/[^/]+/[^/]+', array('controller' => 'row\applets\jobs\Controller', 'action' => 'job'));

// If a controller is specified, but no action, the first match ((.*) in this case) is used for actionPath
// If specified like this, a 'module' must be set, so the Controller knows where it's located (in this case: /jobs...)
$router->add('/jobs(.*)', array('controller' => 'row\applets\jobs\Controller', 'module' => 'jobs'));


