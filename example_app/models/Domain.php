<?php

namespace app\models;

use app\specs\Model;

class Domain extends Model {

	static public $events;

	static public $_table = 'domains';
	static public $_pk = 'domain_id';
	static public $_title = 'domain';
	static public $_getters = array();

}


