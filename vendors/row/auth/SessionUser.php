<?php

namespace row\auth;

use row\core\Object;
use app\models;
use \Exception;

abstract class SessionUser extends Object {

	static public $events;

	static public function user() {
		static $su;
		if ( empty($su) ) {
			$su = new static;
		}
		return $su;
//		return $GLOBALS['application']->user;
	}

	public $user; // typeof Model
//	public $name = 'Anonymous';
//	public $id = 0;

	public $salt; // a string to be filled by ->validate() (from session or database or environment (like hash(ip+ua)) or something)

	public function __construct() {
		$this->_fire('construct', function($self, $args, $chain) {
			$self->validate();
		});
	}

	// Step 1: login (once per session)
	public function login( \row\database\Model $user ) {
		Session::required();
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
		// 1. FIRST: check env
		if ( Session::validateEnvironment() ) { // Includes ::exists() and ::required()
			// 2. Check session
			if ( Session::$session['logins'] ) {
				$login = Session::$session['logins'][count(Session::$session['logins'])-1];
				return $login;
				// 3a. Check database
				// 3b. Register User object in $this
				// 4. Register ACL in $this? Or in _SESSION?
				try {
					/* For instance: *
					$user = models\SessionUser::one(array(
						'u.user_id' => $login['user_id'],
						'login_sessions.unicheck' => $login['unicheck'],
						'login_sessions.ip' => Session::$session['ip'],
					));
					$this->user = $user;
					$user->saveACL(); // You might wanna lazy-load this with a _GETTER (will be lazy-loaded in $this->hasAccess())
					/**/
				}
				catch ( Exception $ex ) {
					// No $this->user, so no $this->isLoggedIn()
				}
			}
		}
	}

	// Step 3: check login status (many times per HTTP request)
	public function isLoggedIn() {
//		$this->validate(); // Done in __construct
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
		/* For instance: *
		if ( !$this->isLoggedIn() ) {
			return Session::variable($key, $val);
		}
		if ( null !== $val ) {
			if ( !isset($login['vars']) ) {
				$login['vars'] = array();
			}
			$login['vars'][$key] = $val;
			return $val;
		}
		$login = Session::$session['logins'][count(Session::$session['logins'])-1];
		return isset($login['vars'][$key]) ? $login['vars'][$key] : null;
		/**/
	}

	// Step 5: logout (once per session)
	public function logout() {
		if ( $this->isLoggedIn() ) {
			// Alter _SESSION
			// Remove session record in db?
		}
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


