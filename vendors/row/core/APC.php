<?php

class APC {

	static public function key( $name ) {
		return ROW_APP_PATH.'/'.$name;
	}

	static public function get( $name, $alt = null ) {
		if ( function_exists('apc_fetch') ) {
			if ( $var = apc_fetch(static::key($name)) ) {
				return $var;
			}
		}
		return $alt;
	}

	static public function put( $name, $var ) {
		if ( function_exists('apc_store') ) {
			apc_store(static::key($name), $var);
		}
	}

	static public function clear( $name ) {
		if ( function_exists('apc_delete') ) {
			apc_delete(static::key($name));
		}
	}

}


