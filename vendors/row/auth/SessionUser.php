<?php

namespace row\auth;

use row\core\Object;
use app\models;
use \Exception;
use row\auth\Session;

class SessionUser extends Object {

	static public $class = __CLASS__;

	static public function user() {
		static $su;
		if ( empty($su) ) {
			$su = new static::$class;
		}
		return $su;
	}

	static public function Domain() {
		return $_SERVER['HTTP_HOST'];
	}

	static public function IP() {
		return $_SERVER['REMOTE_ADDR'];
	}

	static public function UA() {
		return $_SERVER['HTTP_USER_AGENT'];
	}


	public $user; // typeof Model

	public $salt; // a string to be filled by ->validate() (from session or database or environment (like hash(ip+ua)) or something)

	public $ip = '';
	public $ua = '';

	public $session = array();

	public function __construct() {
		$this->ip = $this::IP();
		$this->ua = $this::UA();

		// Step 0: create Anonymous (once per HTTP request, preferably (?) in the HTTP bootstrap)
		$this->validate();
		$this->session = &Session::$session;

		$GLOBALS['User'] = $this;

		$this->_fire('init');
	}

	protected function _init() {}

	// Step 1: login (once per session)
	public function login( \row\database\Model $user ) {
		$s = Session::$class;
		$s::regenerate();
		$this->session = &$s::$session;

		// Alter _SESSION
		$login = array(
			'user_id' => 0,
			'unicheck' => (string)rand(0, 99999999999),
			'salt' => (string)rand(1000000, 9999999),
			'vars' => array(),
		);

		// Add session record in db?
		$insert = array(
			'user_id' => &$login['user_id'],
			'unicheck' => $login['unicheck'],
			'ip' => $_SERVER['REMOTE_ADDR'],
			'start' => time(),
		);

		return compact('login', 'insert'); // To be used by SessionUser's (instantiable) extention
	}

	// Step 2: validate (once per HTTP request)
	public function validate() {
		$s = Session::$class;

		// 1. FIRST: check env
		if ( $s::validateEnvironment() ) { // Includes ::exists() and ::required()
			// 2. Check session
			if ( $s::$session['logins'] ) {
				$login = $s::$session['logins'][count($s::$session['logins'])-1];
				return $login;
				// 3a. Check database
				// 3b. Register User object in $this
				// 4. Register ACL in $this? Or in _SESSION?
				try {
					/* For instance: *
					$user = models\SessionUser::one(array(
						'u.user_id' => $login['user_id'],
						'login_sessions.unicheck' => $login['unicheck'],
						'login_sessions.ip' => $s::$session['ip'],
					));
					$this->save(array('user' => $user));
					$user->saveACL(); // You might wanna lazy-load this with a _GETTER (will be lazy-loaded in $this->hasAccess())
					/**/
				}
				catch ( Exception $ex ) {
					// No $this->user, so no $this->isLoggedIn()
				}
			}
		}
	}

	// Step 2b: save user stuff to current request
	public function save( $data ) {
		foreach ( $data AS $k => $v ) {
			$this->$k = $v;
		}
	}

	// Step 3: check login status (many times per HTTP request)
	public function isLoggedIn() {
		return !!$this->user; // That easy??
	}

	// Step 4: check access (many times per HTTP request)
	public function hasAccess( $zone ) {
		/* For instance: *
		return $this->isLoggedIn() && $this->user->acl->access($zone);
		/**/
		return false;
	}

	public function variable( $key, $val = null ) {
		$s = Session::$class;

		if ( !$this->isLoggedIn() ) {
			return $s::variable($key, $val);
		}

		$login =& $s::$session['logins'][count($s::$session['logins'])-1];

		if ( null !== $val ) {
			if ( !isset($login['vars']) ) {
				$login['vars'] = array();
			}
			$login['vars'][$key] = $val;
			return $val;
		}

		return isset($login['vars'][$key]) ? $login['vars'][$key] : null;
	}

	// Step 5: logout (once per session)
	public function logout() {
		$s = Session::$class;

		// remove login layer from SESSION
		$loggedOut = (bool)array_pop($s::$session['logins']);

		// regenerate for next login layer
		if ( $s::$session['logins'] ) {
			$s::regenerate();
		}

		return $loggedOut;
	}


	public function displayName() {
		return $this->isLoggedin() ? (string)$this->user : 'Anonymous';
	}

	public function userID() {
		return 0;
	}

	public function __tostring() {
		return $this->displayName();
	}

}


