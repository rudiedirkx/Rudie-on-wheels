<?php

namespace row\utils;

use row\core\Object;

class Options extends Object {

	public function __tostring() {
		return 'Options';
	}

	static public function make( $options, Options $defaults = null ) {
		if ( !is_a($options, 'Options') ) {
			$options = new Options((array)$options, $defaults);
		}
		return $options;
	}

	static public function merge( $base, $specific ) { // Only Options are deep-merged (Arrays aren't)
		foreach ( $specific as $k => $v ) {
			if ( is_a($v, 'Options') && is_a($base->$k, 'Options') ) {
				$v = static::merge($base->$k, $v);
			}
			$base->$k = $v;
		}
		return $base;
	}

	public function __construct( $options, Options $defaults = null ) {
		if ( $defaults ) {
			$options = static::merge($defaults, $options);
		}
		foreach ( $options as $k => $v ) {
			$this->$k = $v;
		}
	}

	public function __get($k) {
		if ( $this->_exists($k) ) {
			return $this->$k;
		}
	}

	public function isEmpty() {
		return 0 == count((array)$this);
	}

}


