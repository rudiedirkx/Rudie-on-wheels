<?php

namespace row\http;

use row\core\Object;
use row\utils\Options;

class Route extends Object {

	public function __tostring() {
		return 'Route';
	}

	public $from = '';
	public $to = '';
	public $options; // typeof Options

	public function __construct( $router, $from, $to, $options = array() ) {
		$this->router = $router;
		$this->from = $from;
		$this->to = $to;
		$this->options = Options::make($options);
	}

	public function resolve( $path ) {
		$from = trim($this->from, '$^ ');
//var_dump($path, $this->from, '--------------------------------------');
		if ( 0 < preg_match('#^'.$from.'$#', $path, $match) ) {
			if ( is_string($this->to) ) {
				$match[0] = $this->to;
				$goto = call_user_func_array('sprintf', $match);
				if ( $this->options->redirect ) {
					header('Location: '.$goto);
					exit;
				}
				return $goto;
			}
			else if ( is_array($this->to) ) {
//print_r($match);
				// Array with location elements? .controller, .action, .arguments
				$this->to['match'] = $match;
				array_shift($match);
				$this->to['arguments'] = isset($this->to['arguments']) ?: $match;
				return $this->to;
			}
		}
	}

}


