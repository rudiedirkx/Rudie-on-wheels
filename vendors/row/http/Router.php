<?php

namespace row\http;

use row\core\Object;
use row\http\Route;

class Router extends Object {

	public function __tostring() {
		return 'Router';
	}

	public $routes = array();

	public $dispatcher;

	public function setDispatcher( $dispatcher ) {
		$this->dispatcher = $dispatcher;
	}

	public function add( $from, $to = null, $options = array() ) {
		$this->routes[] = new Route($this, $from, $to, $options);
	}

	public function resolve( $path ) {
		foreach ( $this->routes AS $route ) {
			if ( $to = $route->resolve('/'.$path) ) {
				return $to;
			}
		}
	}

}


