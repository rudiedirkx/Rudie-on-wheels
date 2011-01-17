<?php

class RowException extends Exception {} // Necessary? Put it where?

class VendorException extends RowException {}

class Vendors {

	static public $defaultLoader;

	static public $vendorPath;

	static public $loaders = array();

	static function init($path) {
		Vendors::$defaultLoader = function($vendor, $class) { // e.g.: "row", "utils\Options"
			$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
			return Vendors::$vendorPath.DIRECTORY_SEPARATOR.$vendor.'/'.$path.'.php';
		};
		Vendors::$vendorPath = $path;
		spl_autoload_register('Vendors::load');
		static::add('row');
	}

	static function add($vendor, $loader = null) {
		if ( !is_callable($loader) ) {
			$loader = Vendors::$defaultLoader;
		}
		Vendors::$loaders[$vendor] = $loader;
	}

	static function load($class) {
		$file = static::class_exists($class);
//var_dump($class, $file);
		if ( $file ) {
			require_once($file);
		}
		else if ( false === $file ) {
//			throw new \VendorException('Could not find class "'.$class.'" ["'.$file.'"]');
		}
	}

	static public function class_exists( $class ) {
		if ( 1 < count($path = explode('\\', $class, 2)) ) {
			$vendor = $path[0];
			if ( isset(\Vendors::$loaders[$vendor]) ) {
				$loader = Vendors::$loaders[$vendor];
				$file = $loader($path[0], $path[1]);
				return !file_exists($file) ? false : $file;
			}
		}
	}


} // END Class Vendors


