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

	public function _exists( $k ) {
		return property_exists($this, $k);
	}

	public function _callable( $method ) {
		return is_callable(array($this, $method));
	}

	public function _fire( $fn, $args = array() ) {
		$fn = '_'.$fn;
		if ( method_exists($this, $fn) ) {
			if ( !isset($args[0]) ) {
				return $this->$fn();
			}
			else if ( !isset($args[1]) ) {
				return $this->$fn($args[0]);
			}
			else {
				return call_user_func_array(array($this, $fn), $args);
			}
		}
	}

}


