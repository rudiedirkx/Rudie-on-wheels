<?php

namespace row\core;

abstract class Object {

	public function _exists($k) {
		return property_exists($this, $k);
	}

	public function _fire( $fn, $args = array() ) {
		$fn = '_'.$fn;
		if ( is_callable(array($this, $fn)) ) {
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


