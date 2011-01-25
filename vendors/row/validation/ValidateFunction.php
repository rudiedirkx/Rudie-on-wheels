<?php

namespace row\validation;

use row\core\Object;

class ValidateFunction extends ValidationRule {

	public $message = '';
	public $function;

	public function __construct( $fn, $msg ) {
		$this->function = $fn;
		$this->message = $msg;
	}

	public function validate( $value ) {
		
	}

}


