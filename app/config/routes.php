<?php

use row\http\Router;
use row\http\Route;

$router = new Router;



// For now: only string -> string routes:

// This is how simple it **can** be
$router->add('/', '/todo', array('redirect' => true));

// Or somewhat more advanced. Notice the reverse arguments: %2 .. %1
$router->add('/record-id/(\d+)/of-table/([^/]+)', '/dbsecrets/table-data/%2/pk/%1');

return;



// Very generic: /controller/action[/arg1[/arg2[...]]]
$router->add('/(?P<controller>[^/]+)/(?P<action>[^/]+)(?P<arguments>.*)', function($match) {
	$args = trim($match['arguments'], '/');
	$match['arguments'] = '' == $args ? array() : explode('/', $args);
	return $match;
});

// Very generic: /controller
$router->add('/(?P<controller>[^/]+)');

// Very common: /
$router->add('/');

//
// How to handle Dispatch Type "specific"?
//

// Quite specific
$router->add('/(?P<controller>(?:users|members|people))/(?P<action>[^/]+)/(\d+)/(?P<andor>(?:and|or))/(\d+)', function($match) {
	$match['arguments'] = array($match[3], $match[5]);
	$match['action'] = $match['action'].'_'.$match['andor'];
	return $match;
});



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
$router->add('/record-id/(\d+)/of-table/([^/]+)', function($match) {
	return array(
		'controller' => 'row\applets\scaffolding\Controller',
		'action' => 'table_record',
		'arguments' => array($match[2], $match[1]), // $match[0] is the complete matched string (so useless)
	);
});

// All match results are passed to the actionFunction as arguments.
// In this case only 1 of 4 'patterns' is an argument: [^/] is variable, but only (\d+) is a result
$router->add('/jobs/[^/]+/(\d+)/[^/]+/[^/]+', array('controller' => 'row\applets\jobs\Controller', 'action' => 'job'));

// If a controller is specified, but no action, the first match ((.*) in this case) is used for actionPath
// If specified like this, a 'module' must be set, so the Controller knows where it's located (in this case: /jobs...)
$router->add('/jobs(.*)', array('controller' => 'row\applets\jobs\Controller', 'module' => 'jobs'));







/*

	// New approach?
	// All routes must return an assoc array to the Dispatcher. The defaults:
		'controller' => 'index',
		'module' => 'index',
		'moduleArguments' => array(),
		'action' => 'index',
		'actionArguments' => array(), // defaults to the regex match results
		'actionPath' => null
	// The Dispatcher then, depending on which keys are set, deduces what Controller and Action to load with what Arguments...
	// To match an actionPath, use: (?P<actionPath>.+)
		isset($module) or $module = $controller;
		if ( isset($controller, $action) ) {
			// This is plenty! Execute!
		}

/**/


