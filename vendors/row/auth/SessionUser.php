<?php

namespace row\auth;

use row\core\Object;

class SessionUser extends Object {

	public function hasAccess() {
		return (bool)rand(0, 1);
	}

}


