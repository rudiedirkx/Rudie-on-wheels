<?php

use row\core\Vendors;

// Application (most importante!)
Vendors::add('app', function($name, $class) {
	$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
	return ROW_APP_PATH.DIRECTORY_SEPARATOR.$path.'.php';
});


