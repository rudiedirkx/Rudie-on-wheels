<?php

namespace app\models;

use row\database\Model;

class User extends Model {

	static public $_table = 'users';
	static public $_pk = 'user_id';

	static public $_getters = array(
		'access_zones' => array( self::GETTER_FUNCTION, true, 'getAccessZones' ),
	);

	public function getGroupId() {
		return 1;
	}

	public function getAccessZones() {
		$iGroupId = $this->getGroupId();
		return self::dbObject()->selectFieldsNumeric('group_access ga, access_zones az', 'access_zone', 'ga.access_zone_id = az.access_zone_id AND ga.access_group_id = ?', array($iGroupId));
	}

}


