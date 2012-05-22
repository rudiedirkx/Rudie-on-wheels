<?php

use row\core\Options;
use row\Output;

function &_cache( $key = null, callable $callback = null, callable $postProcess = null ) {
	static $cache;

	if ( null !== $key ) {
		if ( !isset($cache[$key]) ) {
			$cacheItem = null;

			if ( $callback ) {
				$cacheItem = $callback();

				if ( $postProcess ) {
					$cacheItem = $postProcess($cacheItem, $key);
				}

			}

			$cache[$key] = $cacheItem;
		}

		return $cache[$key];
	}

	return $cache;
}

function redirect( $location, $exit = true ) {
	global $Application;
	return $Application->_redirect($location, $exit);
}

function l( $label, $uri, $options = array() ) {
	$O = Output::$class;

	return $O::link($label, $uri, $options);
}

function t( $str, $replace = array(), $options = array() ) {
	$O = Output::$class;

	return $O::translate($str, $replace, $options);
}

function row_array_map( $from, callable $callback ) {
	$to = array();
	foreach ( $from AS $k => $v ) {
		$to[$k] = $callback($v, $k, $from);
	}
	return $to;
}

function row_array_filter( $from, callable $callback ) {
	$to = array();
	foreach ( $from AS $k => $v ) {
		if ( $callback($v, $k, $from) ) {
			$to[$k] = $v;
		}
	}
	return $to;
}

function gmtime() {
	return (int)gmdate('U');
}

function ifsetor( &$var, $alt = null ) {
	return isset($var) ? $var : $alt;
}

function lpad( $val, $len = 2, $pad = '0' ) {
	return str_pad((string)$val, $len, $pad, STR_PAD_LEFT);
}

function rpad( $val, $len = 2, $pad = '0' ) {
	return str_pad((string)$val, $len, $pad, STR_PAD_RIGHT);
}

function options( $options ) {
	return Options::make($options);
}


