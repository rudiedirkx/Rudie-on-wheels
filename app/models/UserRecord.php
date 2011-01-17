<?php

namespace app\models;

use app\models\User;

class UserRecord extends User {

	public function isUnaware() {
		return 0 == rand(0, 3);
	}

}


