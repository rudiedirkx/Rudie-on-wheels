<?php

namespace app\models;

use app\models\User;

class UserRecord extends User {

	static public $_user_accesses = array(); // I save this statically, because it MIGHT happen there are more than 1 User objects per unique user =(

	public function hasAccess( $zone ) {
		// If this user is (the same one as) the SessionUser->user, don't get this from the database, but from the session (HOW??)
		if ( !isset(self::$_user_accesses[$id]) ) {
			self::$_user_accesses[$id] = $this->access_zones;
		}
		$acl = self::$_user_accesses[$id];
		return in_array($zone, $acl);
	}

	public function isUnaware() {
		return 0 == rand(0, 3);
	}

}


