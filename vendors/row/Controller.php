<?php

namespace row;

use row\core\Object;

class Controller extends Object {

	public function __tostring() {
		return 'Controller';
	}

	public $_dispatcher;
	public $_executable = false;
	public $_action = '';
	public $_arguments = array();

	static protected $config = array();

	public function __construct( $dispatcher ) {
		$this->_dispatcher = $dispatcher;
		$this->_action = $dispatcher->_action;
		$this->_arguments = $dispatcher->_arguments;
		$this->_fire('init');
	}

	public function run() { // Always return the Action's return value, so no argument needed
		$this->_fire('pre_load');
		if ( !$this->_executable ) {
			$class = $this->_dispatcher->options->not_found_exception;
			throw new $class($this->_dispatcher->requestPath);
		}
		$this->_fire('pre_action');
		return call_user_func_array(array($this, $this->_action), $this->_arguments);
	}

	public function redirect( $location, $exit = true ) {
//var_dump($location, $this->_dispatcher->requestBasePath);
		$goto = $this->_dispatcher->requestBasePath.'/'.ltrim($location, '/');
//exit($goto);
		header('Location: '.$goto);
		if ( $exit ) {
			exit;
		}
	}

	static public function config( $key, $fallback = null ) {
		if ( isset(static::$config[$key]) ) {
			return static::$config[$key];
		}
		return $fallback;
	}

}


