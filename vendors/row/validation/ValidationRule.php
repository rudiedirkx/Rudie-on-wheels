<?php

namespace row\validation;

use row\core\Object;

abstract class ValidationRule extends Object {

	public function __tostring() {
		return 'Validation rule';
	}

	public $form; // typeof Validator
	public $field = '';

	public function context( array $form, $field = '' ) {
		$this->form = $form;
		$this->field = $field;
	}

	abstract public function validate( $value );

}


