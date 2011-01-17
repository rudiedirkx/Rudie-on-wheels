<?php

namespace row\utils;

use row\core\Object;

class DateTime extends Object {

	static public $defaultFormat = '';

	public $utc = 0;

	function __construct( $utc ) {
		$this->utc = $utc;
	}

	public function format( $format = null ) {
		if ( !is_string($format) ) {
			$format = static::$defaultFormat;
		}
		return date($format, $this->utc);
	}

}


