<?php

use row\http\Dispatcher;
use row\utils\Options;
use row\http\Router;

require(dirname(__DIR__).'/config/bootstrap.php');

// Vendors configurated...
// And now what?

$options = Options::make(array(
	'dispatch_order' => array('generic' /*, 'specific', 'fallback'*/ ),
//	'ignore_leading_slash' => true,
//	'ignore_trailing_slash' => true,
	'not_found_exception' => 'row\http\NotFoundException',
	'module_class_prefix' => '',
	'module_class_postfix' => 'Controller',
));
$dispatcher = new Dispatcher($options);

$dispatcher->setRouter(new Router); // shortcut =)

try {
	$application = $dispatcher->getApplication( $dispatcher->getRequestPath() ); // typeof Controller
	$response = $application->run();
}
catch ( \row\database\DatabaseException $ex ) {
	exit('[Database (sql?)] '.$ex->getMessage().'');
}
catch ( \row\database\ModelException $ex ) {
	exit('[Model (config?)] '.$ex->getMessage().'');
}
catch ( \row\http\NotFoundException $ex ) {
	exit('[404] Not Found: '.$ex->getMessage().' ('.(int)$ex->getLine().')');
}


