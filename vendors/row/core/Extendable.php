<?php

namespace row\core;

function is_callable( $callback ) {
	if ( is_array($callback) && isset($callback[0], $callback[1]) ) {
		list($obj, $method) = $callback;
		if ( is_a($obj, 'row\core\Extendable') && is_string($method) ) {
			return $obj->_callable($method);
		}
	}
	return \is_callable($callback);
}

abstract class Extendable extends Object {

	static public $_mixins = array();

	public $__mixins = array();

	protected function _init() {
		foreach ( $this::$_mixins AS $class ) {
			if ( class_exists($class) ) {
				$this->__mixins[] = new $class($this);
			}
		}
	}

	// Yuck!?
	public function _callable( $method ) {
		// native method?
		$methods = array_map('strtolower', get_class_methods($this));
		if ( in_array(strtolower($method), $methods) ) {
			return true;
		}

		// mix-in?
		foreach ( $this->__mixins AS $object ) {
			if ( method_exists($object, $method) ) {
				return true;
			}
		}
		return false;
	}

	public function __call( $name, $args ) {
		foreach ( $this->__mixins AS $object ) {
			if ( method_exists($object, $name) ) {
				return call_user_func_array(array($object, $name), $args);
			}
		}
		throw new MethodException(get_class($this).'::'.$name);
	}

}


