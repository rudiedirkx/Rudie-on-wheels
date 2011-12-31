<?php

require(__DIR__.'/base.php');

// always necessary classes
require(ROW_VENDOR_ROW_PATH.'/http/Router.php');
require(ROW_VENDOR_ROW_PATH.'/http/Dispatcher.php');
require(ROW_APP_PATH.'/specs/Dispatcher.php');
require(ROW_VENDOR_ROW_PATH.'/Controller.php');
require(ROW_APP_PATH.'/specs/Controller.php');

require(ROW_VENDOR_ROW_PATH.'/database/QueryResult.php');
require(ROW_VENDOR_ROW_PATH.'/database/Model.php');
require(ROW_APP_PATH.'/specs/Model.php');
require(ROW_VENDOR_ROW_PATH.'/auth/SessionUser.php');
require(ROW_APP_PATH.'/specs/SessionUser.php');

// init database
require(__DIR__.'/database.php');

// include configured routes
require(__DIR__.'/routes.php');

// include configured controllers
require(__DIR__.'/controllers.php');

// global functions
require(ROW_VENDOR_ROW_PATH.'/core/_functions.php');


