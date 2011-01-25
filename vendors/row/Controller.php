<?php

namespace row;

use row\core\Object;
use row\utils\Options;

class Controller extends Object {

	public function __tostring() {
		return 'Controller';
	}

	public $_dispatcher;
	public $_executable = false;
	public $_action = '';
	public $_arguments = array();
	static protected $_actions = false; // Must be Array

	static protected $config = array();

	public function __construct( $dispatcher ) {
		$this->_dispatcher = $dispatcher;
		$this->_uri = $this->_dispatcher->requestPath;
//		$this->_action = $dispatcher->_action;
//		$this->_arguments = $dispatcher->_arguments;
//		$this->_fire('init'); // This seems premature then as well...
		$this->post = Options::make($_POST);
		$this->get = Options::make($_GET);
	}

	public function _getActionPaths() {
		return static::$_actions;
	}

	public function _run() { // Always return the Action's return value, so no argument needed
		$this->_arguments = $this->_dispatcher->_moduleArguments;
		$this->_fire('pre_action');
		$r = call_user_func_array(array($this, $this->_dispatcher->_action), $this->_dispatcher->_actionArguments);
		$this->_fire('post_action');
		return $r;
	}

	protected function redirect( $location, $exit = true ) {
		$goto = $this->_dispatcher->requestBasePath.'/'.ltrim($location, '/');
		header('Location: '.$goto);
		if ( $exit ) {
			exit;
		}
	}

	static protected function config( $key, $fallback = null ) {
		if ( isset(static::$config[$key]) ) {
			return static::$config[$key];
		}
		return $fallback;
	}

}


