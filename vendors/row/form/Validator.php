<?php

namespace row\form;

use row\core\Options;

class Validator extends \row\core\Object {

	static public $events;

	static public $dateRegex = '\d{4}\-\d\d?-\d\d?';

	static public $timeRegex = '\d\d?:\d\d';

	public function time( $validator, $field, $options ) {
		return $this->regex($validator, $field, array('pattern' => $this::$timeRegex));
	}

	public function date( $validator, $field, $options ) {
		return $this->regex($validator, $field, array('pattern' => $this::$dateRegex));
	}

	public function integer( $validator, $field, $options ) {
		if ( !isset($validator->input[$field]) ) {
			return false;
		}
		$int = $validator->input[$field];
		if ( (string)$int !== (string)(int)$int ) {
			return false;
		}
		$validator->output[$field] = (int)$int;
	}

	public function number( $validator, $field, $options ) {
		if ( !isset($validator->input[$field]) || !is_numeric($number = $validator->input[$field]) ) {
			return false;
		}
		$validator->output[$field] = (float)$number;
	}

	public function boolean( $validator, $field, $options ) {
		$validator->output[$field] = !empty($validator->input[$field]);
	}

	public function oneOfOptions( $validator, $field, $options ) {
		if ( !isset($validator->input[$field]) ) return false;
		$options = Options::make($options);
		$value = $validator->input[$field];
		$allowedValues = array_map(function($val) {
			return is_a($val, 'row\database\Model') ? implode(',', $val->_pkValue()) : $val;
		}, (array)$options->options);
		return in_array($value, $allowedValues);
	}

	public function regex( $validator, $field, $options ) {
		if ( !isset($validator->input[$field]) ) return false;
		$options = Options::make($options);
		$pattern = $options->pattern ?: $options->regex ?: $options->regexp;
		$pattern = '#^'.$pattern.'$#';
		return 0 < preg_match($pattern, $validator->input[$field]);
	}

	public function remove( $validator, $field ) {
		unset($validator->output[$field]);
	}

	public function allow() {
		return true;
	}

	public function notEmpty( $validator, $field, $options = array() ) {
		if ( !isset($validator->input[$field]) ) return false;
		$options = Options::make($options);
		$length = is_array($validator->input[$field]) ? count($validator->input[$field]) : strlen(trim((string)$validator->input[$field]));
		$min = $options->min ?: 1;
		return $length >= $min;
	}

	public function someNotEmpty( $validator, $field, $options = array() ) {
		$options = Options::make($options);
		if ( is_array($options->fields) ) {
			$min = $options->min ?: 1;
			$notEmpty = 0;
			foreach ( $options->fields AS $f ) {
				$notEmpty += (int)$this->notEmpty($validator, $f, $options);
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
		$this->options = Options::make($options, Options::make(array(
			'errors' => Options::make(array(
				'notEmpty' => 'Must submit value',
				'regex' => 'Invalid value format',
			))
		)));
	}

	public function validate( $input, &$context = array() ) {
		$this->input = $input;
		$this->context =& $context;
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
				$validatorType = is_string($rule['validator']) ? $rule['validator'] : 'custom';
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
							$this->errors[$field][] = isset($rule['message']) ? $rule['message'] : $this->options->errors->get($validatorType, $this->options->get('default_error', $this->defaultError));
//							return false;
						}
						else if ( $field && isset($this->input[$field]) && !isset($this->output[$field]) ) {
							$this->output[$field] = $this->input[$field];
						}
					}
				}
			}
			if ( !empty($this->errors) ) {
//				return false;
			}
		}
		return empty($this->errors);
	}

	public function setError( $field, $error ) {
		foreach ( (array)$field AS $f ) {
			$this->errors[$f][] = $error;
		}
	}

	public function getError( $field ) {
		return isset($this->errors[$field]) ? $this->errors[$field][0] : '';
	}

	public function ifError( $field, $error = 'error', $noError = '' ) {
		return !empty($this->errors[$field]) ? $error : $noError;
	}

	public function valueFor( $field, $alt = '' ) {
		return isset($this->input[$field]) ? $this->input[$field] : $alt;
	}

}


