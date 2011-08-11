<?php

namespace row\auth;

use row\core\Object;
use row\auth\SessionUser;

class Session extends Object {

	static public $class = __CLASS__;

	static public $name = 'row_4_0';

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
		$sname = ini_get('session.name');
		if ( isset($_POST[$sname]) ) {
			session_id($_POST[$sname]);
		}

		$su = SessionUser::$class;
		session_set_cookie_params(0, $GLOBALS['Dispatcher']->requestBasePath, $su::Domain(), false, true);
	}

	static public function exists() {
		$sname = ini_get('session.name');
		$exists = !empty($_SESSION) || isset($_COOKIE[$sname]) || isset($_POST[$sname]);
		if ( $exists ) {
			static::required();
			return true;
		}
	}

	static public function required() {
		$sid = session_id();
		if ( !$sid ) {

			// Session not started for this request

			static::preInit();

			session_start();

			if ( !isset($_SESSION[static::$name], $_SESSION[static::$name]['ip'], $_SESSION[static::$name]['ua']) ) {
				// Session not started for this session
				$_SESSION[static::$name] = array(
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

			static::$session =& $_SESSION[static::$name];
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


