<?php

namespace row\core;

use \Exception;
use \Closure;

class RowException extends Exception {}
class ChainException extends RowException {}

abstract class Object {

	static public $chain; // typeof Array<Chain>

	static public function event( $type, Closure $event = null, $name = '' ) {
		// add 1 Event to several types
		if ( is_array($type) ) {
			foreach ( $type AS $t ) {
				static::event($t, $event);
			}
			return;
		}

		// create new Chain for this type
		if ( !isset(static::$chain[$type]) ) {
			static::$chain[$type] = new Chain($type, get_called_class());
		}
		// check Chain's base class
		else {
			if ( get_called_class() !== static::$chain[$type]->class ) {
				throw new ChainException("Class '".get_called_class()."' wasn't configured (correctly) for Events");
			}
		}

		// return Chain
		if ( null === $event ) {
			return static::$chain[$type];
		}

		// add 1 Event to 1 type
		return static::$chain[$type]->add($event, $name);
	}

	public function _chain( $type, Closure $event = null, array $args = array() ) {
		$event or $event = function() {};

		$chain = static::event($type);
		$chain->first($event);

		return $chain->start($this, options($args));
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


