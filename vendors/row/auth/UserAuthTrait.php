<?php

namespace row\auth;

trait UserAuthTrait {

	static public function withCredentials( Array $credentials ) {
		$db = static::dbObject();

		$pkColumn = static::$_pk;
		$pkColumn = $db->escapeAndQuoteColumn($pkColumn);

		$credentials['password'] = array(
			"SHA1(CONCAT(".$pkColumn.", ':', ?, ':', ?))", // the SQL
			$credentials['password'], // the arguments
			ROW_APP_SECRET,
		);

		return static::one($credentials);
	}

	public function setPassword( $password ) {
		$pk = $this->_pkValue();
		$id = implode(',', $pk);

		$update = array(
			'password' => sha1($id . ':' . $password . ':' . ROW_APP_SECRET),
		);
		// don't `this->update`, because that might've been overridden by the app
		return self::_update($update, $pk);
	}

}


