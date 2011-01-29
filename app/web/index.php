<?php

$_start = microtime(1);

use row\http\Dispatcher;
use row\utils\Options;
use row\http\Router;

// Config Vendors, database, ... ?
require(dirname(__DIR__).'/config/bootstrap.php');

// Config Dispatcher
$options = array(
//	'module_delim' => '/',

	'module_class_prefix' => '',
	'module_class_postfix' => 'Controller',

//	'default_module' => 'Ooeele',
	'ignore_trailing_slash' => true,

/*	'module_to_class_translation' => function($module) {
//		$module = ucfirst(row\utils\Inflector::camelcase($module));
		return $module;
	}*/
);
$dispatcher = new Dispatcher($options);

// Enable routes (available through config/routes.php through config/bootstrap.php)
$dispatcher->setRouter($router);

try {
	$application = $dispatcher->getApplication( $dispatcher->getRequestPath() ); // typeof Controller
	// Everything's checked now... Invalid URI's would be intercepted.
	// All there's left to do is push the red button:
	// 1) fire _pre_action, 2) execute action, 3) fire _post_action
	$response = $application->_run();
//	echo "\ndone in: ".number_format(microtime(1)-$_start, 4);
//	print_r($_SESSION);
}
catch ( \row\http\NotFoundException $ex ) {
	$trace = $ex->getTrace();
	$throw = $trace[0];
	exit('[404] Not Found: '.$ex->getMessage().' ('.(int)$throw['line'].')');
}
// All other exceptions SHOULD have been caught within...
catch ( \row\database\DatabaseException $ex ) {
	exit('[Database (sql?)] '.$ex->getMessage().'');
}
catch ( \row\database\ModelException $ex ) {
	exit('[Model (config?)] '.$ex->getMessage().'');
}


