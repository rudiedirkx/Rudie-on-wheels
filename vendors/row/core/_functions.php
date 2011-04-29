<?php

function gmtime() {
	return gmdate('U');
}

function ifsetor( &$var, $alt = null ) {
	return isset($var) ? $var : $alt;
}


