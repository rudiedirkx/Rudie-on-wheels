<?php

namespace row\form;

use row\utils\Options;

class Validator extends \row\core\Object {

	public function notEmpty( $validator, $field ) {
		return !empty($validator->input[$field]) && '' != trim((string)$validator->input[$field]);
	}

	public $rules = array();
	public $options; // typeof Options
	public $defaultError = 'Invalid value';

	public $input = array();
	public $context;
	public $errors = array();
	public $warnings = array();
	public $output = array();

	public function __construct( $rules, $options = array() ) {
		$this->rules = $rules;
		$this->options = Options::make($options);
	}

	public function validate( $input, $context = null ) {
		$this->input = $input;
		$this->context = $context;
		foreach ( $this->rules AS $rule ) {
			$fn = $rule['validator'];
			if ( !is_callable($fn) ) {
				if ( $this->options->model && method_exists($this->options->model, (string)$fn) ) {
					$fn = array($this->options->model, (string)$fn);
				}
				else if ( !is_callable($this, $fn) ) {
					$fn = array($this, (string)$fn);
				}
			}
			if ( is_callable($fn) ) {
				foreach ( (array)$rule['fields'] AS $field ) {
					if ( is_string($error = call_user_func($fn, $this, $field)) ) {
						$this->errors[$field][] = $error;
					}
					else if ( true !== $error ) {
						$this->errors[$field][] = $this->options->get('default_error', $this->defaultError);
					}
					else if ( !isset($this->output[$field]) ) {
						$this->output[$field] = $this->input[$field];
					}
				}
			}
		}
		return empty($this->errors);
	}

	public function ifError( $field, $error = 'error', $noError = '' ) {
		return !empty($this->errors[$field]) ? $error : $noError;
	}

}


