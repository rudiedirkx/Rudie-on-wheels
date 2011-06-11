<?php

use row\core\Vendors;

// Application (most importante!)
Vendors::add('app', function($name, $class) {
	$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
	return ROW_APP_PATH.DIRECTORY_SEPARATOR.$path.'.php';
});

/* Symfony package (why??) *
Vendors::add('symfony', function($vendor, $class) {
	$file = ROW_VENDORS_PATH.'/doctrine-orm/Doctrine/Symfony/'.str_replace('\\', '/', $class).'.php';
	return $file;
});

/* Doctrine ORM package (part of Symfony I'm sure) *
Vendors::add('doctrine', function($vendor, $class) {
	$file = ROW_VENDORS_PATH.'/doctrine-orm/Doctrine/'.str_replace('\\', '/', $class).'.php';
	return $file;
});
/**/

// Zend (testing underscore class based vendors)
Vendors::add('zend', function($vendor, $class) {
	$file = ROW_VENDORS_PATH.'/ZendTEST/framework/library/Zend/'.str_replace('_', '/', $class).'.php';
	return $file;
});

