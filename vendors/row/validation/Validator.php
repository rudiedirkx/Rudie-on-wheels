<?php

namespace row\validation;

use row\core\Object;

class Validator extends Object {

	public function __tostring() {
		return 'Validator';
	}

	public function __construct($form) {
		$this->form = $form;
	}

	public function validate( $data ) { // $data is probably (a part of) $_POST
		foreach ( $this->form AS $fName => $field ) {
			if ( is_int($fName) ) {
				foreach ( $field AS $rule ) {
					if ( is_a($rule, 'row\\validation\\ValidationRule') ) {
						$rule->context($this->form);
						$rule->validate($data);
					}
				}
			}
			else if ( !empty($field['rules']) ) {
				foreach ( $field['rules'] AS $rule ) {
					if ( is_a($rule, 'row\\validation\\ValidationRule') ) {
						$rule->context($this->form, $fName);
						$rule->validate($data);
					}
				}
			}
		}
	}

}


