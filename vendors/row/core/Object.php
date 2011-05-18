<?php

namespace row\core;

use \Exception;
use \Closure;

class RowException extends Exception {}

class ChainException extends RowException {}

abstract class Object {

	static public $events; // typeof Array<Chain>

	static public function event( $type, Closure $event = null, $name = '' ) {
		// add 1 Event to several types
		if ( is_array($type) ) {
			foreach ( $type AS $t ) {
				static::event($t, $event);
			}
			return;
		}

		// create new Chain for this type
		if ( !isset(static::$events[$type]) ) {
			static::$events[$type] = new Chain($type, get_called_class());
		}
		// check Chain's base class
/*		else {
			if ( get_called_class() !== static::$events[$type]->class ) {
				throw new ChainException("Class '".get_called_class()."' wasn't configured (correctly) for Events");
			}
		}*/

		// return Chain
		if ( null === $event ) {
			return static::$events[$type];
		}

		// add 1 Event to 1 type
		return static::$events[$type]->add($event, $name);
	}

	public function _fire( $type, $native = null, array $args = array() ) {
		$fn = '_'.$type;
		if ( method_exists($this, $fn) ) {
			return call_user_func_array(array($this, $fn), (array)$native);
		}

		if ( !$native ) {
			$native = function() {}; // something to close the cycle
		}

		$chain = static::event($type);
		$chain->first($native);
		return $chain->start($this, Options::make($args));
	}

/*	public function _fire( $fn, $args = array() ) {
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
	}*/


	protected function _init() {}


	public function _exists( $k ) {
		return property_exists($this, $k);
	}

	public function _callable( $method ) {
		return is_callable(array($this, $method));
	}


} // END Class Object


