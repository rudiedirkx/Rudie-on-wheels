<?php

define('APP_VENDORS', dirname(dirname(dirname(__FILE__))).'/vendors');
//echo APP_VENDORS."\n";
require(dirname(__FILE__).'/../config/bootstrap.php');
//echo APP_FOLDER."\n";
//echo APP_NAME."\n";


/* move to where? =( */
class RowException extends Exception {}
class VendorException extends RowException {}
class Vendors {
	static public $default_loader;
	static public $vendor_path;
	static function init($path) {
		Vendors::$default_loader = function($vendor, $class) { // e.g.: row\utils\Options
			$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
			return Vendors::$vendor_path.DIRECTORY_SEPARATOR.$vendor.'/'.$path.'.php';
		};
		Vendors::$vendor_path = $path;
		spl_autoload_register('Vendors::load');
		static::add('row');
	}
	static public $loaders = array();
	static function add($vendor, $loader = null) {
		if ( !is_callable($loader) ) {
			$loader = Vendors::$default_loader;
		}
		Vendors::$loaders[$vendor] = $loader;
	}
	static function load($class) {
		if ( 1 < count($path = explode('\\', $class, 2)) ) {
			$vendor = $path[0];
			if ( isset(Vendors::$loaders[$vendor]) ) {
				$loader = Vendors::$loaders[$vendor];
				$file = $loader($path[0], $path[1]);
				if ( !file_exists($file) ) {
					throw new VendorException('Could not find class "'.$class.'" of vendor "'.$vendor.'".');
				}
				include($file);
			}
		}
	}
}
/**/

Vendors::init(APP_VENDORS);
Vendors::add('app', function($name, $class) {
	$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
	return APP_FOLDER.DIRECTORY_SEPARATOR.$path.'.php';
});

// So is a Bootstrap class really necessary?
// How to do this in a cronjob environment?
$config = new \row\core\Bootstrap(array(
	'autoload' => $autoload
)); // autoloading etc

$dispatcher = $config->getDispatcher(); // typeof Dispatcher

$application = $dispatcher->getApplication( /*$dispatcher->getRequestURI()*/ ); // typeof Controller
$application->run(true);


