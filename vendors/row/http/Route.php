<?php

namespace row\http;

use row\core\Object;

class Route extends Object {

	public function __tostring() {
		return 'Route';
	}

	public $from = '';
	public $to = '';

	public function __construct( $from, $to ) {
		$this->from = $from;
		$this->to = $to;
	}

	public function resolve( $path ) {
		$path = '/'.$path;
		$from = trim($this->from, '$^ ');
//var_dump($from, $path);
		if ( 0 < preg_match('#^'.$from.'$#', $path, $match) ) {
			$match[0] = $this->to;
//print_r($match); exit;
			return call_user_func_array('sprintf', $match);
		}
	}

}


