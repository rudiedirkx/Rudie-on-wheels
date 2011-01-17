<?php

namespace row\validation;

use row\core\Object;

abstract class ValidationRule extends Object {

	abstract public function validate();

}


