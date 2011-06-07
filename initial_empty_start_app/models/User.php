<?php

namespace app\models;

use app\specs\Model;

class User extends Model {

	static public $_table = 'users';
	static public $_pk = 'user_id';
	static public $_getters = array();

	public function __tostring() {
		return 'User # '.$this->user_id;
	}

}


