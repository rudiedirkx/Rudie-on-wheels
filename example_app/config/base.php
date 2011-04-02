<?php

define( 'ROW_APP_PATH', dirname(__DIR__) ); // The application root
define( 'ROW_PATH', dirname(ROW_APP_PATH) ); // Absolute root
define( 'ROW_VENDORS_PATH', ROW_PATH.'/vendors' ); // Folders with all the vendor folders
define( 'ROW_VENDOR_ROW_PATH', ROW_VENDORS_PATH.'/row' ); // The folder for the framework vendor Rudie On Wheels

// core classes
require(ROW_VENDOR_ROW_PATH.'/core/Object.php');
require(ROW_VENDOR_ROW_PATH.'/core/Options.php');
require(ROW_VENDOR_ROW_PATH.'/core/Vendors.php');
require(ROW_VENDOR_ROW_PATH.'/core/APC.php');

// init vendor class
Vendors::init(ROW_VENDORS_PATH);

// include more vendors (or not)
require(__DIR__.'/vendors.php');


