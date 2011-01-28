<?php

namespace app\specs;

use row\auth\Session;
use app\models;

/**
 * This class overwrites (but uses) the framework's base SessionUser,
 * because the framework doesn't know how YOU (= the app) want to log
 * in and verify sessions. It also doesn't know what the database
 * looks like and how you named your columns.
 * 
 * This class knows all that, because you made it =)
 * 
 * You can do more app-specific things here. A few standard things are
 * built into the framework: session security (with IP and UA), session
 * init, salt security (partly) and it prepared Auth login and validation.
 */

class SessionUser extends \row\auth\SessionUser {

	public function hasAccess( $zone ) {
		return $this->isLoggedIn() && ( in_array(strtolower($zone), $this->user->acl) || in_array('everything', $this->user->acl) );
	}

	public function displayName() {
		return $this->isLoggedin() ? (string)$this->user->full_name : 'Anonymous';
	}

	public function logout() {
		if ( $this->isLoggedIn() ) {
			array_pop(Session::$session['logins']);
			Session::success('Well done matey! Now you\'re gonne have to log-back-in!?');
		}
	}

	public function login( $user ) {
		$login = parent::login($user);
		extract($login); // Extracts arrays $login and $insert

		// Prepare session login layer
		$login['user_id'] = $user->user_id;

		// Add login to _SESSION
		Session::$session['logins'][] = $login;

		// isLoggedIn() for this HTTP request
		$this->user = $user;

		return true; // Why would this ever be false??
	}

	public function validate() {
		$login = parent::validate();
//print_r($login);
		if ( is_array($login) && isset($login['user_id'], $login['salt']) ) {
			try {
				$this->user = models\User::get($login['user_id']);
				$this->salt = $login['salt'];
//				$this->id = $this->user->user_id;
//				$this->name = $this->user->full_name;
			}
			catch ( \Exception $ex ) {}
		}
//print_r($this);
	}

}


