<?php

namespace row\validation;

use row\core\Object;

abstract class ValidationRule extends Object {

	public function __tostring() {
		return 'Validation rule';
	}

	abstract public function validate();

}


