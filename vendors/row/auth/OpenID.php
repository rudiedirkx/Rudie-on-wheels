<?php

namespace row\auth;

use row\core\Object;

abstract class OpenID extends Object {

	static public $providerTypes = array(
		'facebook' => 'row\auth\openid\FacebookConnect',
		'openid' => 'row\auth\openid\OpenID',
	);

	/**
	// I'd like:

	// step 1
	abstract public function login();

	// step 2
	abstract public function validate();

	// step 3
	abstract public function userInfo();
	/**/

}


