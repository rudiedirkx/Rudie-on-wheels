<?php

require(__DIR__.'/base.php');

// always necessary classes
require(ROW_VENDOR_ROW_PATH.'/http/Router.php');
require(ROW_VENDOR_ROW_PATH.'/http/Dispatcher.php');
require(ROW_APP_PATH.'/specs/Dispatcher.php');
require(ROW_VENDOR_ROW_PATH.'/Controller.php');
require(ROW_APP_PATH.'/specs/Controller.php');

// init database
require(__DIR__.'/database.php');

// include configured routes
require(__DIR__.'/routes.php');

// global functions
require(ROW_VENDOR_ROW_PATH.'/core/_functions.php');


