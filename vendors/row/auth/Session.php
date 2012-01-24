<?php

namespace row\auth;

use row\core\Object;
use row\auth\SessionUser;

class Session extends Object {

	static public $class = __CLASS__;

	static public $name = 'row1';

	static public $id; // PHP's session id
	static public $session;

	static public function variable( $k, $v = null ) {
		if ( null !== $v ) {
			static::required();
			static::$session['vars'][$k] = $v;
			return $v;
		}

		if ( static::validateEnvironment() ) {
			return isset(static::$session['vars'][$k]) ? static::$session['vars'][$k] : null;
		}
	}

	static public function validateEnvironment() {
		static::preInit();

		if ( static::exists() ) {
			$su = SessionUser::$class;
			// Check IP
			if ( isset(static::$session['ip']) && sha1($su::IP()) === static::$session['ip'] ) {
				// Check User Agent
				if ( isset(static::$session['ua']) && sha1($su::UA()) === static::$session['ua'] ) {
					return true;
				}
			}
		}
	}

	static public function preInit() {
		static $inited = false;

		if ( !$inited ) {
			$inited = true;

			$sname = static::$name;
			session_name($sname);

#			$su = SessionUser::$class;
#			session_set_cookie_params(0, $GLOBALS['Dispatcher']->requestBasePath, $su::Domain(), false, false);
		}
	}

	static public function destroy() {
		if ( static::exists() ) {
			session_destroy();
		}
	}

	static public function regenerate() {
		static::required();

		session_regenerate_id(true);
	}

	static public function exists() {
		static::preInit();

		$sname = static::$name;
		$exists = !empty($_SESSION) || isset($_COOKIE[$sname]) || isset($_POST[$sname]);
		if ( $exists ) {
			static::required();
			return true;
		}
	}

	static public function required() {
		static::preInit();

		$sid = session_id();
		if ( !$sid ) {

			session_start();
			$sid = session_id();

			if ( !isset($_SESSION['ip'], $_SESSION['logins']) ) {
				// Session not started for this session
				$_SESSION = array(
					'ip' => sha1($_SERVER['REMOTE_ADDR']),
					'ua' => sha1($_SERVER['HTTP_USER_AGENT']),
					'start' => time(),
					'active' => time(),
					'messages' => array(),
					'logins' => array(),
					'vars' => array(),
				);
			}
			else {
				
			}

			static::$session =& $_SESSION;
			static::$id = session_id();

		}
	}

	static public function messages( $clear = true ) {
		if ( !static::exists() ) {
			return array();
		}
		$messages = static::$session['messages'];
		if ( $clear ) {
			static::$session['messages'] = array();
		}
		return $messages;
	}

	static public function error( $msg ) {
		return static::message($msg, 'error');
	}

	static public function warning( $msg ) {
		return static::message($msg, 'warning');
	}

	static public function success( $msg ) {
		return static::message($msg, 'success');
	}

	static public function message( $msg, $type = 'info' ) {
		static::required();
		static::$session['messages'][] = array($msg, $type);
	}

}


