<?php

namespace row\auth;

use row\database\Model;

class AbstractUser extends Model {

	static public function withCredentials( Array $credentials ) {
		$db = static::dbObject();

		$pkColumn = static::$_pk;
		$pkColumn = $db->escapeAndQuoteColumn($pkColumn);

		$credentials['password'] = array(
			"SHA1(CONCAT(".$pkColumn.", ':', ?, ':', ?))",
			$credentials['password'],
			ROW_APP_SECRET,
		);
print_r($credentials);

		return static::one($credentials);
	}

	public function setPassword( $password ) {
		$id = $this->_pkValue();
		$id = $id[0];

		return $this->update(array(
			'password' => sha1($id . ':' . $password . ':' . ROW_APP_SECRET),
		));
	}

}


