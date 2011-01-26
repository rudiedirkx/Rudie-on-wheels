<?php

namespace app\specs;

use row\auth\Session;
use app\models;

class SessionUser extends \row\auth\SessionUser {

	public function displayName() {
		return $this->isLoggedin() ? (string)$this->user->full_name : 'Anonymous';
	}

	public function logout() {
		if ( $this->isLoggedIn() ) {
			array_pop(Session::$session['logins']);
			Session::success('Goed gedaan jonge, nou moet je weer opnieuw inloggen!?');
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
			}
			catch ( \Exception $ex ) {}
		}
//print_r($this);
	}

}


