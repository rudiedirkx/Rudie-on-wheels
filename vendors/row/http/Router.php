<?php

namespace row\http;

use row\core\Object;
use row\http\Route;

class Router extends Object {

	public function __tostring() {
		return 'Router';
	}

	public $routes = array();

	public function add( $from, $to ) {
		$this->routes[] = new Route($from, $to);
	}

	public function resolve( $path ) {
		foreach ( $this->routes AS $route ) {
			if ( is_string($to = $route->resolve($path)) ) {
				return $to;
			}
		}
	}

}


