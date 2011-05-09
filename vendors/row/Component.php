<?php

namespace row;

use row\core\Options;

class Component extends \row\core\Extendable {

	public $application;

	final public function __construct( \row\Controller $application, $options = array() ) {
		$this->application = $application;
		$this->options = Options::make($options);
		$this->_fire('init');
	}

}


