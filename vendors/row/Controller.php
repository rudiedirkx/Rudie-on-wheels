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

abstract class Controller extends Object {

	public $_dispatcher;

	static protected $_actions = false; // Must be an Array to use "specfic" type Dispatching

	protected $_config = array();
	static protected $config = array();

	public function __construct( $dispatcher ) {
		$this->_dispatcher = $dispatcher;
		$this->_uri = substr($this->_dispatcher->requestPath, 1);

		$this->post = Options::make($_POST);
		$this->get = Options::make($_GET);
	}

	protected function _init() {
	}

	protected function _pre_action() {
//		$this->acl->check($this->_dispatcher->_action);
	}

	protected function _post_action() {
	}

	public function _getActionPaths() {
		return static::$_actions;
	}

	public function _run() { // Always return the Action's return value
//		$this->_arguments = $this->_dispatcher->_moduleArguments; // deprecated
		$this->_fire('pre_action');
		$r = call_user_func_array(array($this, $this->_dispatcher->_action), $this->_dispatcher->_actionArguments);
		$this->_fire('post_action');
		return $r;
	}

	protected function _redirect( $location, $exit = true ) {
		$goto = $this->_dispatcher->requestBasePath.'/'.ltrim($location, '/');
		header('Location: '.$goto);
		if ( $exit ) {
			exit;
		}
	}

	protected function _download( $filename, $contentType = 'text/plain' ) {
		header('Content-type: '.$contentType);
		header('Content-Disposition: attachment; filename="'.addslashes($filename).'"');
	}

	protected function _config( $key, $fallback = null ) {
		if ( isset($this->_config[$key]) ) {
			return $this->_config[$key];
		}
		return $fallback;
	}
	protected function _configs() {
		return $this->_config;
	}

	static protected function config( $key, $fallback = null ) {
		if ( isset(static::$config[$key]) ) {
			return static::$config[$key];
		}
		return $fallback;
	}
	static protected function configs() {
		return static::$config;
	}

	static protected function ajax() {
		return !empty($_SERVER['HTTP_AJAX']);
	}

	static protected function post() {
		return isset($_SERVER['HTTP_METHOD']) && 'POST' === $_SERVER['HTTP_METHOD'];
	}

}


