<?php

namespace row\core;

class RowException extends \Exception {}
class NotFoundException extends RowException {}
class OutputException extends RowException {}
class VendorException extends RowException {}
class MethodException extends RowException {}

abstract class Object {

	protected function _init() {}

	/* this is not a good idea - `is_callable` will be completely useless *
	static public $_methods = array();

	static public function extend( $name, $function ) {
		$class = strtolower(get_called_class());
		$name = strtolower($name);
		self::$_methods[$class][$name] = $function;
	}

	public function __call( $name, $args ) {
		$name = strtolower($name);
		$class = strtolower(get_class($this));
		array_unshift($args, $this);
		if ( isset(self::$_methods[$class][$name]) ) {
			return call_user_func_array(self::$_methods[$class][$name], $args);
		}
	}
	/**/


	public function _exists( $k ) {
		return property_exists($this, $k);
	}

	public function _fire( $fn, $args = array() ) {
		$fn = '_'.$fn;
		if ( method_exists($this, $fn) ) {
			if ( 0 == count($args) ) {
				return $this->$fn();
			}
/*			else if ( 1 == count($args) ) {
				return $this->$fn($args[0]);
			}
			else if ( 2 == count($args) ) {
				return $this->$fn($args[0], $args[1]);
			}*/
			else {
				return call_user_func_array(array($this, $fn), $args);
			}
		}
	}

}


