<?php

namespace app\specs;

use row\auth\Session;
use app\models;
use \Exception;

class SessionUser extends \row\auth\SessionUser {

	public function login( \row\database\Model $user ) {
		$login = parent::login($user);
		extract($login); // extracts $login and $insert

		$login['user_id'] = $user->id;

	}

	public function validate() {
		if ( $session = parent::validate() ) {
			// check database?
			// or dump it:
			print_r($session);
		}
	}

	public function hasAccess( $zone ) {
		return false;
	}

	public function userID() {
		return $this->user->id;
	}

	public function logout() {
		// remove login layer
		if ( $session = array_pop(Session::$session['logins']) ) {
			// Remove session record in db?
		}
	}

}


