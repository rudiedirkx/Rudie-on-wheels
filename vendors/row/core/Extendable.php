<?php

namespace row\core\Object;

class Extendable extends Object {

	static public $_extensions = array();

	static public function _extend( $name, $function ) {
		static::$_extensions[$name] = $function;
	}

	public function __call( $name, $arguments ) {
		if ( isset(static::$_extensions[$name]) ) {
			return call_user_func_array(static::$_extensions[$name], $arguments);
		}
		else if ( is_callable(array($this, $name)) ) {
			return call_user_func_array(array($this, $name), $arguments);
		}
	}

}


