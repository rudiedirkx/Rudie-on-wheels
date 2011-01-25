<?php

namespace row\validation;

use row\core\Object;

class ValidateNotEmpty extends ValidationRule {

	public $message = '';

	public function __construct( $msg ) {
		$this->message = $msg;
	}

	public function validate( $value ) {
		return isset($value) && ( is_array($value) || '' != trim($value) );
	}

}


