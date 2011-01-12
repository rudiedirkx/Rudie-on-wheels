<?php

require(dirname(__FILE__).'/../config/bootstrap.php');
//echo APP_FOLDER;

$config = new Bootstrap; // autoloading etc

$dispatcher = $config->getDispatcher(); // typeof Dispatcher

$application = $dispatcher->getApplication( /*$dispatcher->getRequestURI()*/ ); // typeof Controller
$application->run(true);


