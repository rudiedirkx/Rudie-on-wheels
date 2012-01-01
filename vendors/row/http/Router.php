<?php

namespace row\http;

use row\core\Object;
use row\core\Options;

class Router extends Object {

	public $routes = array();

	public $dispatcher;

	public function setDispatcher( $dispatcher ) {
		$this->dispatcher = $dispatcher;
	}

	public function add( $from, $to = null, $options = array() ) {
		$this->routes[] = (object)array(
			'from' => $from,
			'to' => $to,
			'options' => $options,
		);
	}

	public function routeToRegex( $from, $route ) {
		return $this->dispatcher->routeToRegex($from);
	}

	public function resolve( $path ) {
		foreach ( $this->routes AS $route ) {
			if ( $to = $this->resolveRoute($route, $path) ) {
				return $to;
			}
		}
	}

	public function redirect( $goto, $status = true ) {
		if ( 'http' != substr($goto, 0, 4) && '/' != substr($goto, 0, 1) ) {
			$goto = $this->dispatcher->requestBasePath . $goto;
		}

		if ( is_int($status) ) {
			header('HTTP/1.1 '.$status.' Redirect');
		}

		header('Location: '.$goto);
		exit;
	}

	public function resolveRoute( $route, $uri ) {
		// routes have a leading /
		$path = '/' . $uri;

		// make a nice little regex
		$from = $route->from;
		$from = trim($from, '^ /');
		if ( !$this->dispatcher->options->case_sensitive_paths ) {
			$from = strtolower($from);
			$path = strtolower($path);
		}
		$from = $this->routeToRegex($from, $route);
		$from = '^/' . $from;
		$from = '#' . $from . '#';

		// regex the path
		if ( 0 < preg_match($from, $path, $match) ) {
			// NULL, Array, String or Closure
			$to = $route->to;

			if ( null === $to ) {
				// Array
				$to = $match;
			}
			else if ( is_callable($to) ) {
				// String or Array
				$to = $to($match, $uri);
			}

			if ( is_string($to) ) {
				$options = Options::make($route->options);
				$match[0] = preg_replace('/%(\d+)/', '%\1$s', $to);
				$goto = call_user_func_array('sprintf', $match);
				if ( $options->redirect ) {
					return $this->redirect($goto, $options->redirect);
				}
				return $goto;
			}
			else if ( is_array($to) ) {
				// any implicit arguments?
				if ( !isset($to['arguments']) ) {
					// arguments from regex
					if ( 1 < count($match) ) {
						$to['arguments'] = array_slice($match, 1);
					}
				}

				// return Location Array
				return $to;
			}
		}
	}

}


