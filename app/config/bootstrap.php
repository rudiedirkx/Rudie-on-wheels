<?php

define( 'ROW_PATH', dirname(dirname(__DIR__)) ); // Absolute root
define( 'ROW_VENDORS_PATH', ROW_PATH.'/vendors' ); // Folders with all the vendor folders
define( 'ROW_VENDOR_ROW_PATH', ROW_VENDORS_PATH.'/row' ); // The folder for the framework vendor Rudie On Wheels
define( 'ROW_APP_PATH', ROW_PATH.'/app' ); // The application root

require(ROW_VENDOR_ROW_PATH.'/core/Vendors.php');
Vendors::init(ROW_VENDORS_PATH);
Vendors::add('app', function($name, $class) {
	$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
	return ROW_APP_PATH.DIRECTORY_SEPARATOR.$path.'.php';
});

require(__DIR__.'/database.php');


