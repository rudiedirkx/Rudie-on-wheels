<?php

namespace row;

use row\core\Options;
use row\core\Object;

class Component extends Object {

	public $application;
	public $options; // typeof Options

	final public function __construct( \row\Controller $application, $options = array() ) {
		$this->application = $application;
		$this->options = Options::make($options);

		$this->_fire('init');
	}

}


