<?php

class RowException extends Exception {}

class NotFoundException extends \RowException {}

class OutputException extends \RowException {}

class VendorException extends \RowException {}

class Vendors {

	/** experimental **/
	static public $cache = array();
	static public function cacheLoad() {
		static::$cache = APC::get('classes', array());
	}
	static public function cachePut( $class, $file ) {
		static::$cache[$class] = $file;
		APC::put('classes', static::$cache);
	}
	static public function cacheGet( $class ) {
		if ( isset(static::$cache[$class]) ) {
			return static::$cache[$class];
		}
		return false;
	}
	static public function cacheClear() {
		return APC::clear('classes');
	}
	/** experimental **/

	static public $defaultLoader;

	static public $vendorPath;

	static public $loaders = array();

	static public function init($path) {
//var_dump(__METHOD__);
		static::cacheLoad();
		Vendors::$defaultLoader = function( $vendor, $class ) { // e.g.: ( "row", "utils\Options" )
			$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
			return Vendors::$vendorPath.DIRECTORY_SEPARATOR.$vendor.'/'.$path.'.php';
		};
		Vendors::$vendorPath = $path;
		spl_autoload_register('Vendors::load');
		static::add('row');
	}

	static public function add( $vendor, $loader = null ) {
//var_dump(__METHOD__);
		if ( !is_callable($loader) ) {
			$loader = Vendors::$defaultLoader;
		}
		Vendors::$loaders[$vendor] = $loader;
	}

	static public function load( $class ) {
//var_dump(__METHOD__);
		$file = static::cacheGet($class);
		if ( false === $file ) {
			$file = static::class_exists($class);
			static::cachePut($class, $file);
		}
		if ( $file ) {
			require_once($file);
		}
	}

	static public function class_exists( $class ) {
//var_dump(__METHOD__);
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


