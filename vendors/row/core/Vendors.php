<?php

class RowException extends Exception {} // Necessary? Put it where?

class VendorException extends RowException {}

class Vendors {

	/** experimental **/
	static public $cache = array();
	static public function cacheClear() {
		apc_store(static::cacheKey(), array());
	}
	static public function cacheKey() {
//var_dump(__METHOD__);
		return ROW_APP_PATH.'/<classes>';
	}
	static public function cacheLoad() {
//var_dump(__METHOD__);
		if ( function_exists('apc_store') ) {
			$cacheKey = static::cacheKey();
			$cache = apc_fetch($cacheKey) ?: array();
//print_r($cache);
			static::$cache = $cache;
		}
	}
	static public function cachePut( $class, $file ) {
//var_dump(__METHOD__);
		if ( function_exists('apc_store') ) {
			static::$cache[$class] = $file;
			apc_store(static::cacheKey(), static::$cache);
		}
	}
	static public function cacheGet( $class ) {
//var_dump(__METHOD__);
		if ( isset(static::$cache[$class]) ) {
			return static::$cache[$class];
		}
		return false;
	}
	/** experimental **/

	static public $defaultLoader;

	static public $vendorPath;

	static public $loaders = array();

	static public function init($path) {
//var_dump(__METHOD__);
		static::cacheLoad();
		Vendors::$defaultLoader = function($vendor, $class) { // e.g.: "row", "utils\Options"
			$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
			return Vendors::$vendorPath.DIRECTORY_SEPARATOR.$vendor.'/'.$path.'.php';
		};
		Vendors::$vendorPath = $path;
		spl_autoload_register('Vendors::load');
		static::add('row');
	}

	static public function add($vendor, $loader = null) {
//var_dump(__METHOD__);
		if ( !is_callable($loader) ) {
			$loader = Vendors::$defaultLoader;
		}
		Vendors::$loaders[$vendor] = $loader;
	}

	static public function load($class) {
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


