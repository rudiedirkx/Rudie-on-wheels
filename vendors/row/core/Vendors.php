<?php

namespace row\core;

use row\core\APC;
use row\core\RowException;

class VendorException extends RowException {}

class Vendors extends Object {

	// app config //

	/*static public function verifyAppConfig() {
		$required = array('ROW_APP_PATH', 'ROW_PATH', 'ROW_VENDORS_PATH', 'ROW_VENDOR_ROW_PATH');
		foreach ( $required AS $constant ) {
			defined($constant) or die('Not configured: ' . $constant);
		}

		defined('ROW_APP_SECRET') or define('ROW_APP_SECRET', 'NotSecret');
	}*/

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
//		static::verifyAppConfig();

		static::cacheLoad();

		static::$vendorPath = $vendorPath = $path;

		static::$defaultLoader = function( $vendor, $class ) use ( $vendorPath ) { // e.g.: ( "row", "utils\Options" )
			$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
			$path = $vendorPath.DIRECTORY_SEPARATOR.$vendor.DIRECTORY_SEPARATOR.$path.'.php';
			return $path;
		};

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

		// Unknown (new reference)
		if ( null === $file ) {
			// Only cache existing classes (safer?, smaller cache)
			if ( $file = static::class_exists($class) ) {
				static::cachePut($class, $file);
			}
		}

		// Known: class file exists
		if ( $file ) {
			return require_once($file);
		}
		// Known: no class file
		else {
			throw new VendorException($class);
		}
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

		return class_exists($class);
	}


} // END Class Vendors


