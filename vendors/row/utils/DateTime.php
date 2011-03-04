<?php

namespace row\utils;

use row\core\Object;

class DateTime extends Object {

	static public $defaultDateParseFormat = 'y-m-d';

	static public function isDate( $date, $format = '' ) {
		$format or $format = static::$defaultDateParseFormat;
		$regexp = '#^'.strtr(preg_quote($format), array('y' => '(?P<year>(?:1|2)\d{3})', 'm' => '(?P<month>\d\d?)', 'd' => '(?P<day>\d\d?)')).'$#';
		if ( !preg_match($regexp, (string)$date, $match) ) {
			return false;
		}
		$date = $match['year'] . '-' . str_pad((int)$match['month'], 2, '0', STR_PAD_LEFT) . '-' . str_pad((int)$match['day'], 2, '0', STR_PAD_LEFT);
		return $date;
	}

	static public function isDateOrEmpty( $date, $format = '' ) {
		if ( '' === $date ) {
			return null;
		}
		return static::isDate($date, $format);
	}

	static public function isTime( $time, $noMaxHours = true ) {
		if ( !preg_match('/^(\d\d?)(?:(?:\:|\.)(\d\d?))?(?:(?:\:|\.)\d\d?)?(?: ?(am|pm))?$/i', strtolower((string)$time), $match) ) {
			return false;
		}
		$match[] = '';
		$match[] = '';
		list($x, $h, $m, $ampm) = $match;
		$h = (int)$h;
		if ( ('pm' == $ampm && 12 > $h) || ('am' == $ampm && 12 == $h) ) {
			$h += 12;
		}
		if ( !$noMaxHours && 24 <= $h ) {
			$h = $h % 24;
		}
		return str_pad((string)$h, 2, '0', STR_PAD_LEFT) . ':' . str_pad($m, 2, '0', STR_PAD_RIGHT);
	}

	static public function isTimeOrEmpty( $time, $noMaxHours = true ) {
		if ( '' === $time ) {
			return null;
		}
		return static::isTime($time, $noMaxHours);
	}


	public function __tostring() {
		return $this->format();
	}

	static public $defaultFormat = 'Y-m-d H:i:s';

	public $utc = 0;

	function __construct( $time ) {
		$this->utc = is_numeric($time) ? (int)$time : strtotime($time);
	}

	public function format( $format = null ) {
		if ( !is_string($format) ) {
			$format = static::$defaultFormat;
		}
		return date($format, $this->utc);
	}

}


