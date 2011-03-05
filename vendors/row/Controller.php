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
//	public $_action = ''; // deprecated
//	public $_arguments = array(); // deprecated

	protected $_components = array();
	protected $__components = array();

	static protected $_actions = false; // Must be an Array to use "specfic" type Dispatching

	static protected $config = array();

	public function __construct( $dispatcher ) {
		$this->_dispatcher = $dispatcher;
		$this->_uri = substr($this->_dispatcher->requestPath, 1);

//		$this->_action = $dispatcher->_action; // deprecated
//		$this->_arguments = $dispatcher->_arguments; // deprecated

		$this->post = Options::make($_POST);
		$this->get = Options::make($_GET);
	}

	protected function _init() {
		$this->_constructComponents();
	}

	protected function _constructComponents() {
		foreach ( $this->_components AS $k => $c ) {
			$this->$k = $this->getComponent($c[0], isset($c[1]) ? $c[1] : array());
		}
	}

	protected function _destructComponents() {
		foreach ( $this->__components AS $c ) {
			$c->__destruct();
		}
	}

	protected function _pre_action() {
//		$this->acl->check($this->_dispatcher->_action);
	}

	protected function _post_action() {
		$this->_destructComponents();
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

	protected function getComponent( $class, $args = array() ) {
		is_array($args) or $args = (array)$args;
		return $this->__components[] = new $class($this, $args);
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

	static protected function config( $key, $fallback = null ) {
		if ( isset(static::$config[$key]) ) {
			return static::$config[$key];
		}
		return $fallback;
	}

	static protected function ajax() {
		return !empty($_SERVER['HTTP_AJAX']);
	}

	static protected function post() {
		return isset($_SERVER['HTTP_METHOD']) && 'POST' === $_SERVER['HTTP_METHOD'];
	}

}


