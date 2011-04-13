<?php

// Application (most importante!)
Vendors::add('app', function($name, $class) {
	$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
	return ROW_APP_PATH.DIRECTORY_SEPARATOR.$path.'.php';
});

// phpMarkdownParser
Vendors::add('markdown', function($vendor, $class) {
	$file = ROW_VENDORS_PATH.'/phpMarkdownExtra/'.str_replace('\\', '/', $class).'.php';
	return $file;
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

// Zend
Vendors::add('zend', function($vendor, $class) {
	$file = ROW_VENDORS_PATH.'/ZendTEST/framework/library/Zend/'.str_replace('_', '/', $class).'.php';
	return $file;
});

