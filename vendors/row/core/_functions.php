<?php

use row\core\Options;
use row\Output;

function row_array_map($from, $cb) {
	$to = array();
	foreach ( $from AS $k => $v ) {
		$to[$k] = $cb($v, $k, $from);
	}
	return $to;
}

function row_array_filter($from, $cb) {
	$to = array();
	foreach ( $from AS $k => $v ) {
		if ( $cb($v, $k, $from) ) {
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

function translate( $text, $replace = array(), $options = array() ) {
	$class = Output::$class;
	return $class::translate($text, $replace = array(), $options);
}


