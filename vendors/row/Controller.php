<?php

namespace row;

use row\core\Object;
use row\core\Options;
use row\auth\ControllerACL;

/**
 * All Dispatcher functionality is in the Dispatcher (hey!) so the
 * only thing the Controller does, is run itself.
 * 
 * Also it contains some helper functions (like redirect) and the
 * application's base Controller will probably contain many more.
 */

class Controller extends Object {

	public function __tostring() {
		return 'Controller';
	}

	public $_dispatcher;
//	public $_action = ''; // deprecated
//	public $_arguments = array(); // deprecated

	static protected $_actions = false; // Must be an Array to use "specfic" type Dispatching

	static protected $config = array();

	public function __construct( $dispatcher ) {
		$this->_dispatcher = $dispatcher;
		$this->_uri = $this->_dispatcher->requestPath;

//		$this->_action = $dispatcher->_action; // deprecated
//		$this->_arguments = $dispatcher->_arguments; // deprecated

		$this->post = Options::make($_POST);
		$this->get = Options::make($_GET);
	}

	// Easily overwritable (to remove or extend below functionality)
	protected function _init() {
		$this->acl = new ControllerACL($this);
	}

	protected function _pre_action() {
		$this->acl->check($this->_dispatcher->_action);
	}

	protected function _post_action() {
		
	}

	public function _getActionPaths() {
		return static::$_actions;
	}

	public function _run() { // Always return the Action's return value, so no argument needed
//		$this->_arguments = $this->_dispatcher->_moduleArguments; // deprecated
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


