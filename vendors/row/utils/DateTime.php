<?php

namespace row\utils;

use row\core\Object;

class DateTime extends Object {

	static public $default_format = '';

	public $utc = 0;

	function __construct( $utc ) {
		$this->utc = $utc;
	}

	public function format( $format = null ) {
		if ( !is_string($format) ) {
			$format = static::$default_format;
		}
		return date($format, $this->utc);
	}

}


