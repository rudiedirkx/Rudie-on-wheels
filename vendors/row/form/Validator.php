<?php

namespace row\form;

use row\utils\Options;

class Validator extends \row\core\Object {

	public function allow() {
		return true;
	}

	public function notEmpty( $validator, $field, $options = array() ) {
		return !empty($validator->input[$field]) && '' != trim((string)$validator->input[$field]);
	}

	public function someNotEmpty( $validator, $field, $options = array() ) {
		$options = Options::make($options);
		if ( is_array($options->fields) ) {
			$min = $options->min ?: 1;
			$notEmpty = 0;
			foreach ( $options->fields AS $f ) {
				$notEmpty += (int)$this->notEmpty($validator, $f);
			}
			if ( $notEmpty >= $min ) {
				return true;
			}
			$this->setError($options->fields, 'Require at least '.$min.' of: '.implode(', ', $options->fields));
		}
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
			$fields = isset($rule['field']) ? (array)$rule['field'] : array(0);
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
				unset($rule['field'], $rule['validator']);
				foreach ( $fields AS $field ) {
					if ( !$field || empty($this->errors[$field]) ) {
						if ( is_string($error = call_user_func($fn, $this, $field, $rule)) ) {
							$this->errors[$field][] = $error;
//							return false;
						}
						else if ( null === $error ) {
							// Ignore?
						}
						else if ( true !== $error ) {
							$this->errors[$field][] = $this->options->get('default_error', $this->defaultError);
//							return false;
						}
						else if ( $field && isset($this->input[$field]) && !isset($this->output[$field]) ) {
							$this->output[$field] = $this->input[$field];
						}
					}
				}
			}
		}
		return empty($this->errors);
	}

	public function setError( $field, $error ) {
		foreach ( (array)$field AS $f ) {
			$this->errors[$f][] = $error;
		}
	}

	public function ifError( $field, $error = 'error', $noError = '' ) {
		return !empty($this->errors[$field]) ? $error : $noError;
	}

	public function valueFor( $field, $alt = '' ) {
		return isset($this->input[$field]) ? $this->input[$field] : $alt;
	}

}


