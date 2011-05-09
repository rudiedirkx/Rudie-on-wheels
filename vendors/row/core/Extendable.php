<?php

namespace row\core;

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

	public function __call( $name, $args ) {
		foreach ( $this->__mixins AS $object ) {
			if ( method_exists($object, $name) ) {
				return call_user_func_array(array($object, $name), $args);
			}
		}
		throw new MethodException(get_class($this).'::'.$name);
	}

}


