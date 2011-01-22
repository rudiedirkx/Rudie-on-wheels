<?php

namespace row\http;

use row\core\Object;

class Route extends Object {

	public function __tostring() {
		return 'Route';
	}

	public $from = '';
	public $to = '';
	public $redirect = false;

	public function __construct( $router, $from, $to, $redirect = false ) {
		$this->router = $router;
		$this->from = $from;
		$this->to = $to;
		$this->redirect = $redirect;
	}

	public function resolve( $path ) {
		$from = trim($this->from, '$^ ');
		if ( 0 < preg_match('#^'.$from.'$#', $path, $match) ) {
			if ( is_string($this->to) ) {
				$match[0] = $this->to;
				$goto = call_user_func_array('sprintf', $match);
				if ( $this->redirect ) {
					header('Location: '.$goto);
					exit;
				}
				return $goto;
			}
			else if ( is_array($this->to) ) {
				// Array with location elements? .controller, .action, .arguments
				isset($this->to['controller']) or $this->to['controller'] = $this->router->dispatcher->options->default_module;
				isset($this->to['action']) or $this->to['action'] = $this->router->dispatcher->options->default_action;
				isset($this->to['arguments']) or $this->to['arguments'] = array();
				return $this->to;
			}
		}
	}

}


