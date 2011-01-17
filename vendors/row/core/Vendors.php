<?php

class RowException extends Exception {} // Necessary? Put it where?

class VendorException extends RowException {}

class Vendors {

	static public $default_loader;

	static public $vendor_path;

	static public $loaders = array();

	static function init($path) {
		Vendors::$default_loader = function($vendor, $class) { // e.g.: "row", "utils\Options"
			$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
			return Vendors::$vendor_path.DIRECTORY_SEPARATOR.$vendor.'/'.$path.'.php';
		};
		Vendors::$vendor_path = $path;
		spl_autoload_register('Vendors::load');
		static::add('row');
	}

	static function add($vendor, $loader = null) {
		if ( !is_callable($loader) ) {
			$loader = Vendors::$default_loader;
		}
		Vendors::$loaders[$vendor] = $loader;
	}

	static function load($class) {
		$file = static::class_exists($class);
//var_dump($class, $file);
		if ( $file ) {
			include($file);
		}
		else if ( false === $file ) {
			throw new \VendorException('Could not find class "'.$class.'" ["'.$file.'"]');
		}
/*		if ( 1 < count($path = explode('\\', $class, 2)) ) {
			$vendor = $path[0];
			if ( isset(Vendors::$loaders[$vendor]) ) {
				$loader = Vendors::$loaders[$vendor];
				$file = $loader($path[0], $path[1]);
				if ( !file_exists($file) ) {
					throw new VendorException('Could not find class "'.$class.'" ["'.$file.'"]');
				}
				include($file);
			}
		}*/
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


