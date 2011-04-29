<?php

namespace row;

use row\core\Object;
use row\core\Options;
use row\auth\SessionUser;

/**
 * All Dispatcher functionality is in the Dispatcher (hey!) so the
 * only thing the Controller does, is run itself.
 * 
 * Also it contains some helper functions (like redirect) and the
 * application's base Controller will probably contain many more.
 */

abstract class Controller extends Object {

	public $_dispatcher;
	public $_uri;
	public $_response;

	public $post; // typeof Options
	public $get; // typeof Options

	protected $_actions = false; // Must be an Array to use "specfic" type Dispatching

	protected $config = array();

	public function __construct( $dispatcher ) {
		$this->_dispatcher = $dispatcher;
		$this->_uri = substr($this->_dispatcher->requestPath, 1);
	}


	// events
	protected function _init() {
		// overridable _POST and _GET
		$this->post = Options::make($_POST);
		$this->get = Options::make($_GET);
	}

	protected function _pre_action() {
		// check acl
		$this->aclCheck();
	}

	protected function _post_action() {
		// display view?
		if ( is_string($this->_response) ) {
			exit($this->_response);
		}
	}


	public function _getActionPaths() {
		return $this->_actions;
	}


	// run entire controller
	public function _run() {
		$this->_fire('pre_action');

		$this->_response = call_user_func_array(array($this, $this->_dispatcher->_action), $this->_dispatcher->_actionArguments);

		$this->_fire('post_action');

		return $this->_response;
	}


	// helpers
	protected function _redirect( $location, $exit = true ) {
		$goto = 0 === strpos($location, '/') || in_array(substr($location, 0, 6), array('http:/', 'https:')) ? $location : Output::url($location);
		header('Location: '.$goto);
		if ( $exit ) {
			exit;
		}
	}

	protected function _download( $filename, $contentType = 'text/plain' ) {
		header('Content-type: '.$contentType);
		header('Content-Disposition: attachment; filename="'.addslashes($filename).'"');
	}


	// config
	protected function _config( $key, $fallback = null ) {
		if ( isset($this->config[$key]) ) {
			return $this->config[$key];
		}
		return $fallback;
	}


	// environment
	public function _ajax() {
		return	!empty($_SERVER['HTTP_AJAX']) OR
				!empty($_SERVER['HTTP_X_AJAX']) OR
				( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) );
	}

	public function _post() {
		return isset($_SERVER['REQUEST_METHOD']) && 'POST' === $_SERVER['REQUEST_METHOD'];
	}


	// acl
	protected $acl = array();

	public function aclAdd( $zones, $actions = null ) {
		if ( !$actions ) {
			// get all public methods (because those are actions) of the application
			$refl = new \ReflectionClass($this);
			$methods = $refl->getMethods();
			$actions = array();
			foreach ( $methods AS $m ) {
				if ( $m->isPublic() && '_' != substr($m->name, 0, 1) ) {
					$actions[] = $m->name;
				}
			}
		}
		foreach ( (array)$zones AS $zone ) {
			foreach ( (array)$actions AS $action ) {
				$this->acl[$action][$zone] = true;
			}
		}
	}

	public function aclRemove( $zones, $actions ) {
		foreach ( (array)$zones AS $zone ) {
			foreach ( (array)$actions AS $action ) {
				unset($this->acl[$action][$zone]);
			}
		}
	}

	public function aclCheck( $action = '' ) {
		$action or $action = $this->_dispatcher->_action;
		if ( isset($this->acl[$action]) ) {
			foreach ( $this->acl[$action] AS $zone => $x ) {
				if ( !$this->aclCheckAccess($zone) ) {
					return $this->aclAccessFail( $zone, $action );
				}
			}
		}
		return true;
	}

	public function aclCheckAccess( $zone ) {
		return true;
	}

	protected function aclAccessFail( $zone, $action ) {
		return false;
	}

}


