<?php

namespace row\auth;

use row\core\Object;

class Session extends Object {

	static public $name = 'row_4_0'; // Change this frequently!

	static public $session;

	static public function variable( $k, $v = null ) {
		if ( 2 <= func_num_args() ) {
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
			// Check IP
			if ( isset(static::$session['ip'], $_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] == static::$session['ip'] ) {
				// Check User Agent
				if ( isset(static::$session['ua'], $_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] == static::$session['ua'] ) {
//					static::$session['active'] = time(); // Let's not...
					return true;
				}
			}
		}
	}

	static public function exists() {
		$sname = ini_get('session.name');
		$exists = isset($_COOKIE[$sname]) || isset($_POST['SID']);
		if ( $exists ) {
			static::required();
			return true;
		}
	}

	static public function required() {
		$sid = session_id();
		if ( !$sid ) { // Session not started for this request
			if ( isset($_POST['SID']) ) {
				session_id($_POST['SID']);
			}
//echo 'reviving session with session_start'."\n";
			session_start();
			if ( !isset($_SESSION[static::$name], $_SESSION[static::$name]['ip'], $_SESSION[static::$name]['ua']) ) { // Session not started for this session
				$_SESSION[static::$name] = array(
					'ip' => $_SERVER['REMOTE_ADDR'],
					'ua' => $_SERVER['HTTP_USER_AGENT'],
					'start' => time(),
					'active' => time(),
					'messages' => array(),
					'logins' => array(),
					'vars' => array(),
				);
//echo 'session reset with new vars'."\n";
			}
			else {
//echo 'valid session found, so don\'t change it'."\n";
				
			}
			static::$session =& $_SESSION[static::$name];
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


