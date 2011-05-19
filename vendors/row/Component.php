<?php

namespace row;

use row\core\Options;
//use row\core\Extendable AS ComponentParent;
use row\core\Object AS ComponentParent;

abstract class Component extends ComponentParent {

	static public $events;

	public $application;

	public function __construct( \row\Controller $application, $options = array() ) {

		$this->_fire('construct', function($self, $args, $chain) {
			$self->application = $args->application;
			$self->options = Options::make($args->options);
		}, compact('application', 'options'));
	}

}


