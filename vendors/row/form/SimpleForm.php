<?php

namespace row\form;

use row\core\Options;
use row\utils\Inflector;
use row\Output;

class SimpleForm extends \row\Component {

	const NOT_REQUIRED = 0;
	const MUST_EXIST = 1;
	const NOT_EMPTY = 2;
	const MORE_THAN_WHITESPACE = 3;

	public $input = array();
	public $errors = array();
	public $output = array();


	public function validate( $data ) {
		$this->input = $data;

		$this->_elements = $this->elements();
		$validators = $errors = array();

		foreach ( $this->_elements AS $name => &$element ) {
			$element['name'] = $name;
			$this->elementTitle($element);
			if ( is_string($name) ) {
				// form element
				if ( !empty($element['required']) ) {
					if ( !$this->validateRequired($name, $element) ) {
						$this->errors[$name][] = $this->errorMessage('required', $element);
					}
				}
				if ( !empty($element['regex']) && empty($this->errors[$name]) ) {
					$match = preg_match('/^'.$element['regex'].'$/', (string)$this->input($name, ''));
					if ( !$match ) {
						$this->errors[$name][] = $this->errorMessage('regex', $element);
					}
				}
				if ( !empty($element['validation']) && empty($this->errors[$name]) ) {
					$fn = $element['validation'];
					$r = $fn($this);
					if ( false === $r || is_string($r) ) {
						$this->errors[$name][] = false === $r ? $this->errorMessage('custom', $element) : $r;
					}
				}
			}
			else {
				// validator
				$validators[] = $element;
			}
			unset($element);
		}

		if ( 0 == count($this->errors) ) {
			foreach ( $validators AS $validator ) {
				if ( empty($validator['require']) || 0 == count(array_intersect((array)$validator['require'], array_keys($this->errors))) ) {
//echo "\n  do custom validator on [".implode(', ', (array)$validator['fields'])."] ...\n";
					$v = $validator['validation'];
					if ( !$v($this) ) {
						$error = $this->errorMessage('custom', $validator);
						foreach ( (array)$validator['fields'] AS $name ) {
							$this->errors[$name][] = $error;
						}
					}
				}
			}
		}

		return 0 == count($this->errors);
	}

	public function validateRequired( $name, $element ) {
		if ( !isset($this->input[$name]) ) {
			return false;
		}
		$length = is_array($this->input[$name]) ? count($this->input[$name]) : strlen(trim((string)$this->input[$name]));
		$minlength = isset($element['minlength']) ? (int)$element['minlength'] : 1;
		return $length >= $minlength;
	}

	public function errorMessage( $type, $element ) {
		$this->elementTitle($element);
		$title = $element['title'];

		switch ( $type ) {
			case 'required':
				return 'Field "'.$title.'" is required';
			break;
			case 'regex':
				return 'Field "'.$title.'" has invalid format';
			break;
			case 'custom':
				$fields = !isset($element['fields']) ? array($element['name']) : (array)$element['fields'];
				foreach ( $fields AS &$name ) {
					$name = $this->_elements[$name]['title'];
					unset($name);
				}
				return isset($element['message']) ? $element['message'] : 'Custom validation failed for: "'.implode('", "', $fields).'"';
			break;
		}

		return 'Unknown type of error for field "'.$title.'".';
	}

	public function errors() {
		$errors = array();
		foreach ( $this->errors AS $errs ) {
			$errors = array_merge($errors, array_filter($errs));
		}
		return array_unique($errors);
	}

	public function error( $elementName, $error = ' error', $noError = ' no-error' ) {
		return isset($this->errors[$elementName]) ? $error : $noError;
	}


	public function input( $name, $alt = '' ) {
		return isset($this->input[$name]) ? $this->input[$name] : $alt;
	}

	public function output( $name, $value = null ) {
		if ( null !== $value ) {
			return $this->output[$name] = $value;
		}
		return isset($this->output[$name]) ? $this->output[$name] : '';
	}



	public function renderDropdownElement( $name, $element ) {
		$value = $this->input($name, 0);

		$html = '<select name="'.$name.'">';
		foreach ( (array)$element['options'] AS $k => $v ) {
			if ( is_a($v, '\row\database\Model') ) {
				$k = implode(',', $v->_pkValue());
			}
			$html .= '<option value="'.$k.'"'.( (string)$k === $value ? ' selected' : '' ).'>'.$v.'</option>';
		}
		$html .= '</select>';

		return $this->renderElementWrapper($html, $element);
	}

	public function renderTextElement( $name, $element ) {
		$type = $element['type'];
		$value = $this->input($name);

		$html = '<input type="'.$type.'" name="'.$name.'" value="'.$value.'" />';

		return $this->renderElementWrapper($html, $element);
	}

	public function renderTextareaElement( $name, $element ) {
		$value = $this->input($name);
		$options = Options::make($element);
		$rows = $options->rows ? ' rows="'.$options->rows.'"' : '';
		$cols = $options->cols ? ' cols="'.$options->cols.'"' : '';
		$html = '<textarea'.$rows.$cols.' name="'.$name.'">'.$value.'</textarea>';
		return $this->renderElementWrapper($html, $element);
	}

	public function renderMarkupElement( $name, $element ) {
		$options = Options::make(Options::make($element));
		$inside = $options->inside ?: $options->text;
		if ( $inside ) {
			return '<p class="form-element markup '.$name.'">'.$inside.'</p>';
		}
		else if ( $options->outside ) {
			return $options->outside;
		}
		return '';
	}

	// This method is now absolutely useless as without it the exact same thing would happen
	public function renderPasswordElement( $name, $element ) {
		$element['type'] = 'password';
		return $this->renderTextElement($name, $element);
	}



	public function render( $withForm = true, $options = array() ) {
		$elements = $this->elements();
		$index = 0;
		$html = '';
		foreach ( $elements AS $name => $element ) {
			if ( is_string($name) ) {
				$element['name'] = $name;
				$element['index'] = $index++;
				$html .= $this->renderElement($name, $element);
				$html .= $this->elementSeparator();
			}
		}
		if ( $withForm ) {
			$options = Options::make($options);
			$method = $options->get('action', 'post');
			$action = Output::url($this->application->_uri);
			$html =
				'<form method="'.$method.'" action="'.$action.'">' .
					$this->elementSeparator() .
					$html.$this->renderButtons() .
					$this->elementSeparator() .
				'</form>';
		}
		return $html;
	}

	public function renderElement( $name, $element ) {
		$this->elementTitle($element);

		$type = $element['type'];
		if ( empty($type) ) {
			return '';
		}
		$renderMethod = array($this, 'render'.ucfirst($type).'Element');
		if ( !empty($element['render']) ) {
			$renderMethod = $element['render'];
		}
		if ( is_callable($renderMethod) ) {
			return call_user_func($renderMethod, $name, $element);
		}

		return $this->renderTextElement($name, $element);
	}

	public function renderButtons() {
		return '<p class="form-submit"><input type=submit></p>';
	}

	public function renderElementWrapper( $input, $element ) {
		$name = $element['name'];
		$description = empty($element['description']) ? '' : '<span class="description">'.$element['description'].'</span>';
		return '<p class="form-element '.$element['type'].' '.$name.$this->error($name).'"><label>'.$element['title'].'</label><span class="input">'.$input.'</span>'.$description.'</p>';
	}

	public function elementSeparator() {
		return "\n\n";
	}



	public function elementTitle( &$element ) {
		if ( empty($element['title']) ) {
			$element['title'] = $this->nameToTitle($element['name']);
		}
	}

	public function nameToTitle( $name ) {
		return Inflector::spacify($name); // 'beautify'
	}



	public function __tostring() {
		return $this->render();
	}


}


