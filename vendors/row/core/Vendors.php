<?php

namespace row\core;

use row\core\APC;
use row\core\RowException;

class VendorException extends RowException {}

class Vendors extends Object {

	// cache //

	static public $cache = array();
	static public $cacheChanged = false;

	static public function cacheLoad() {
		if ( array() === static::$cache ) {
			static::$cache = APC::get('classes', array());
			register_shutdown_function(function() {
				if ( Vendors::$cacheChanged ) {
					APC::put('classes', Vendors::$cache);
				}
			});
		}
	}

	static public function cachePut( $class, $file ) {
		if ( false !== static::$cache ) {
//var_dump(__METHOD__, $class, $file, '');
			static::$cache[$class] = $file;
			static::$cacheChanged = true;
		}
	}

	static public function cacheGet( $class ) {
		if ( isset(static::$cache[$class]) ) {
			return static::$cache[$class];
		}
	}

	static public function cacheClear() {
		return APC::clear('classes');
	}

	// loaders //

	static public $defaultLoader;

	static public $vendorPath;

	static public $loaders = array();

	static public function init($path) {
		static::cacheLoad();
		static::$defaultLoader = function( $vendor, $class ) { // e.g.: ( "row", "utils\Options" )
			$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
			return Vendors::$vendorPath.DIRECTORY_SEPARATOR.$vendor.'/'.$path.'.php';
		};
		static::$vendorPath = $path;
		spl_autoload_register('row\core\Vendors::load');
		static::add('row');
	}

	static public function add( $vendor, $loader = null ) {
		if ( !is_callable($loader) ) {
			$loader = static::$defaultLoader;
		}
		static::$loaders[strtolower($vendor)] = $loader;
	}

	static public function load( $class ) {
		$file = static::cacheGet($class); // NULL, FALSE or String classFile
		if ( null === $file ) {
			// Unknown (new reference)
			if ( $file = static::class_exists($class) ) {
				// Only cache existing classes (safer?, smaller cache)
				static::cachePut($class, $file);
			}
		}
		if ( $file ) {
			// Known: class file exists
			require_once($file);
		}
		// Known: no class file
	}

	static public function class_exists( $class ) {
		if ( 1 < count($path = explode('\\', $class, 2)) || 1 < count($path = explode('_', $class, 2)) ) {
			$vendor = strtolower($path[0]);
			if ( isset(static::$loaders[$vendor]) ) {
				$loader = static::$loaders[$vendor];
				$file = $loader($path[0], $path[1]);
				return !file_exists($file) ? false : $file;
			}
		}
	}


} // END Class Vendors


