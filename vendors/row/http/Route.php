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

	public function __construct( $router, $from, $to = null, $options = array() ) {
		$this->router = $router;
		$this->from = $from;
		$this->to = $to;
		$this->options = $options;
	}

	public function resolve( $path ) {
		$from = '/'.trim($this->from, '$^ /');
//var_dump($path, $this->from, '--------------------------------------');
		if ( 0 < preg_match('#^'.$from.'$#', $path, $match) ) {
			$to = $this->to;
			if ( null === $to ) {
				$to = $match;
			}
			else if ( is_callable($to) ) {
				$to = $to($match);
			}
			if ( is_string($to) ) {
				$options = Options::make($this->options);
				$match[0] = preg_replace('/%(\d+)/', '%\1$s', $to);
				$goto = call_user_func_array('sprintf', $match);
				if ( $options->redirect ) {
					header('Location: '.$goto);
					exit;
				}
				return $goto;
			}
			else if ( is_array($to) ) {
				$to['actionArguments'] = !isset($to['arguments']) ? array_slice($match, 1) : (array)$to['arguments'];
				unset($to['arguments']);
				return $to;
			}
		}
	}

}


