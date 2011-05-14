<?php

namespace row\core;

use \Exception;
use \Closure;

class RowException extends Exception {}

abstract class Object {

	static public $events; // typeof Chain

	static public function event( $type, Closure $event = null ) {
		if ( is_array($type) ) {
			// several types, same event
			foreach ( $type AS $t ) {
				static::event($t, $event);
			}
			return;
		}

		if ( !isset(static::$events[$type]) ) {
			static::$events[$type] = new Chain($type, get_called_class());
		}

		if ( null === $event ) {
			return static::$events[$type];
		}

		return static::$events[$type]->add($event);
	}

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


