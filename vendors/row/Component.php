<?php

namespace row;

use row\core\Object;

class Component extends Object {

	protected $application;

	final public function __construct( \row\Controller $application, $args = array() ) {
		$this->application = $application;
		$this->_fire('init');
	}

	public function _init() {
		
	}

	public function __destruct() {
		
	}

}


