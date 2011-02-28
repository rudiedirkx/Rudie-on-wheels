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
		$this->routes[] = array(
			'from' => $from,
			'to' => $to,
			'options' => $options,
		);
	}

	public function resolve( $path ) {
		foreach ( $this->routes AS $route ) {
			if ( $to = $this->resolveRoute($route, '/'.$path) ) {
				return $to;
			}
		}
	}

	public function redirect( $goto ) {
		if ( 'http' != substr($goto, 0, 4) && '/' != substr($goto, 0, 1) ) {
			$goto = $this->dispatcher->requestBasePath.'/'.$goto;
		}
		header('Location: '.$goto);
		exit;
	}

	public function resolveRoute( $route, $path ) {
		$route = (object)$route;
		$from = '^/'.trim($route->from, '^ /');
		if ( 0 < preg_match('#'.$from.'#', $path, $match) ) {
			$to = $route->to;
			if ( null === $to ) {
				$to = $match;
			}
			else if ( is_callable($to) ) {
				$to = $to($match);
			}
//var_dump($to); exit;
			if ( is_string($to) ) {
				$options = Options::make($route->options);
				$match[0] = preg_replace('/%(\d+)/', '%\1$s', $to);
				$goto = call_user_func_array('sprintf', $match);
				if ( $options->redirect ) {
					return $this->redirect($goto);
				}
				return $goto;
			}
			else if ( is_array($to) ) {
				if ( isset($to['arguments']) ) {
					$to['actionArguments'] = (array)$to['arguments'];
					unset($to['arguments']);
				}
				else if ( 1 < count($match) ) {
					$to['actionArguments'] = array_slice($match, 1);
				}
				return $to;
			}
		}
	}

}


