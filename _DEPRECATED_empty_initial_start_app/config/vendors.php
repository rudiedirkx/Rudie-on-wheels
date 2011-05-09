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


