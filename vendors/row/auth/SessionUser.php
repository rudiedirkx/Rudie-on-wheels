<?php

namespace row\auth;

use row\core\Object;

class SessionUser extends Object {

	public $anonymous = true; // will only be true for very first layer

	public function hasAccess() {
		return (bool)rand(0, 1);
	}

	public function logout() {
		// Do what exactly? Invalidate session layer how? Have a standard (ROW) implementation or let APP handle fully?
		// Change _SESSION here or in auth\Session?
	}

	public function displayName() {
		return 'Anonymous';
	}

	public function __tostring() {
		return 'SessionUser: ' . $this->displayName();
	}

}


