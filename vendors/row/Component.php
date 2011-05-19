<?php

namespace row;

use row\core\Options;
//use row\core\Extendable AS ComponentParent;
use row\core\Object AS ComponentParent;

class Component extends ComponentParent {

	public $application;

	final public function __construct( \row\Controller $application, $options = array() ) {
		$this->application = $application;
		$this->options = Options::make($options);
		$this->_fire('init');
	}

}


