<?php

namespace row;

use row\core\Object;

class Controller extends Object {

	public $_dispatcher;
	public $_executable = false;
	public $_action = '';
	public $_arguments = array();

	static public $config = array();

	public function __construct( $action, $arguments ) {
		$this->_action = $action;
		$this->_arguments = $arguments;
	}

	public function run() { // Always return the Action's return value, so no argument needed
		if ( !$this->_executable ) {
			$class = $this->_dispatcher->options->not_found_exception;
			throw new $class('Invalid request URI.');
		}
		return call_user_func_array(array($this, $this->_action), $this->_arguments);
	}

	static public function config( $key, $fallback = null ) {
		if ( isset(static::$config[$key]) ) {
			return static::$config[$key];
		}
		return $fallback;
	}

}


