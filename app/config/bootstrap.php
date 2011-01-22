<?php

define( 'ROW_PATH', dirname(dirname(__DIR__)) ); // Absolute root
define( 'ROW_VENDORS_PATH', ROW_PATH.'/vendors' ); // Folders with all the vendor folders
define( 'ROW_VENDOR_ROW_PATH', ROW_VENDORS_PATH.'/row' ); // The folder for the framework vendor Rudie On Wheels
define( 'ROW_APP_PATH', ROW_PATH.'/app' ); // The application root

// require(ROW_VENDOR_ROW_PATH.'/core/_functions.php'); // Useless crap!

require(ROW_VENDOR_ROW_PATH.'/core/Vendors.php');
Vendors::init(ROW_VENDORS_PATH);

require(__DIR__.'/vendors.php');

require(__DIR__.'/database.php');

if ( isset($_SERVER['HTTP_HOST']) ) { // http
	require(__DIR__.'/routes.php');
}


