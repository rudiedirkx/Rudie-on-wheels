<?php

namespace row\utils;

use row\core\Object;

class DateTime extends Object {

	public function __tostring() {
		return $this->format();
	}

	static public $defaultFormat = 'Y-m-d H:i:s';

	public $utc = 0;

	function __construct( $time ) {
		$this->utc = \is_numeric($time) ? (int)$time : strtotime($time);
	}

	public function format( $format = null ) {
		if ( !is_string($format) ) {
			$format = static::$defaultFormat;
		}
		return date($format, $this->utc);
	}

}


