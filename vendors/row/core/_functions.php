<?php

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


