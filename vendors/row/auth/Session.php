<?php

namespace row\auth;

use row\core\Object;
//use row\auth;

class Session extends Object {

	public $users = array();

	public function __construct() {
		$this->users[] = new SessionUser; // There is ALWAYS at least one 'layer'
	}

	public function addUser( SessionUser $user ) {
		$this->users[] = $user;
	}

	public function logout() {
		$user = $this->getUser();
		if ( !$user->anonymous ) {
			array_pop($this->users);
			$user->logout();
			// Change _SESSION here or in auth\SessionUser?
			return true;
		}
		// Can't 'logout' Anonymous user. Must always have that very first layer
	}

	public function getUser() {
		$n = count($this->users);
		return $this->users[$n-1];
	}

	public function __tostring() {
		return 'Session';
	}

}


