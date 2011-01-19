<?php

namespace row\http;

use row\core\Object;

class Router extends Object {

	public function __tostring() {
		return 'Router';
	}

	public $routes = array();

	public function add( $from, $to ) {
		
	}

	public function resolve( $path ) {
		foreach ( $this->routes AS $route ) {
			if ( false !== ($to = $route->resolve($path)) ) {
				return $to;
			}
		}
	}

}


