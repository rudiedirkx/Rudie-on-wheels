<?php

namespace row\form;

use row\core\Options;
use row\utils\Inflector;
use row\Output;

abstract class SimpleForm extends \row\Component {

	public $_elements = array(); // internal cache
	abstract protected function elements( $defaults = null );

	public $default = null;
	public $input = array();
	public $errors = array();
	public $output = array();

	public $inlineErrors = true;
	public $elementWrapperTag = 'div';
	public $renderers = array();


	public function validate( $data ) {
		$this->input = $data;

		$elements =& $this->useElements();
		$validators = $output = array();

		$this->_fire('pre_validate');

		function murlencode($name, $k, $arr) {
			$out = array();
			foreach ( $arr AS $k2 => $v ) {
				$out[] = $name . '['.$k.']['.$k2.']=' . urlencode((string)$v);
			}
			return implode('&', $out);
		}

		foreach ( $elements AS $name => &$element ) {
			$element['_name'] = $name;
			$this->elementTitle($element);
			if ( is_string($name) ) {
				// form element
				if ( !empty($element['required']) ) {
					if ( !$this->validateRequired($this, $name) ) {
						$this->errors[$name][] = $this->errorMessage('required', $element);
					}
				}
				if ( !empty($element['regex']) && empty($this->errors[$name]) ) {
					$match = preg_match('/^'.$element['regex'].'$/', (string)$this->input($name, ''));
					if ( !$match ) {
						$this->errors[$name][] = $this->errorMessage('regex', $element);
					}
				}
				if ( !empty($element['validation']) && empty($this->errors[$name]) && $this->input($name) ) {
					$fn = $element['validation'];
					if ( is_string($fn) ) {
						$validationFunction = 'validate'.ucfirst($fn);
						$fn = array($this, $validationFunction);
					}
					if ( is_callable($fn) ) {
						$r = call_user_func($fn, $this, $name);
						if ( false === $r || is_string($r) ) {
							$this->errors[$name][] = false === $r ? $this->errorMessage('custom', $element) : $r;
						}
					}
				}
				if ( isset($this->input[$name]) ) {
					$elName = $this->elementName($element);
					if ( array_key_exists($name, $this->output) ) {
						$input = $this->output[$name];
					}
					else {
						$input = $this->input[$name];
					}
					foreach ( (array)$input AS $k => $v ) {
						$output[] = is_array($v) ? murlencode($elName, $k, $v) : $elName . '=' . urlencode((string)$v);
					}
				}
			}
			else if ( isset($element['validation']) ) {
				// validator
				$validators[] = $element;
			}
			unset($element);
		}
		$output = implode('&', $output);
		$this->output = array();
		parse_str($output, $this->output);

		if ( 0 == count($this->errors) ) {
			foreach ( $validators AS $validator ) {
				if ( empty($validator['require']) || 0 == count(array_intersect((array)$validator['require'], array_keys($this->errors))) ) {
					$v = $validator['validation'];
					$r = $v($this);
					if ( false === $r || is_string($r) ) {
						$error = false === $r ? $this->errorMessage('custom', $validator) : $r;
						foreach ( (array)$validator['fields'] AS $name ) {
							$this->errors[$name][] = $error;
						}
					}
				}
			}
		}

		if ( 0 == count($this->errors) ) {
			$this->_fire('post_validate');
			return true;
		}
		return false;
	}

	public function validateOptions( $form, $name ) {
		$elements =& $this->useElements();
		$element = $elements[$name];
		$value = $this->input($name, '');

		$options = $element['options'];
		foreach ( $options AS $k => $v ) {
			if ( $this->getOptionValue($k, $v) == $value ) {
				return true;
			}
		}
		return false;
	}

	public function validateEmail( $form, $name ) {
		$value = $form->input($name);
		return false !== filter_var($value, FILTER_VALIDATE_EMAIL);
	}

	public function validateDate( $form, $name ) {
		$value = $form->input($name);
		if ( 0 >= preg_match('/^(\d\d\d\d)-(\d\d?)-(\d\d?)$/', $value, $match) ) {
			return false;
		}
		$date = $match[1] . '-' . lpad($match[2]) . '-' . lpad($match[3]);
		$this->output($name, $date);
	}

	public function validateRequired( $form, $name ) {
		if ( !isset($this->input[$name]) ) {
			return false;
		}

		$elements = $this->useElements();
		$element = $elements[$name];

		$length = is_array($this->input[$name]) ? count($this->input[$name]) : strlen(trim((string)$this->input[$name]));
		$minlength = isset($element['minlength']) ? (int)$element['minlength'] : 1;
		return $length >= $minlength;
	}

	public function validateUnique( $form, $name ) {
		$elements = $this->useElements();
		$element = $elements[$name];

		if ( !isset($element['unique'], $element['unique']['model'], $element['unique']['field']) ) {
			// not enough information: autofail
			return false;
		}

		$conditions = isset($element['unique']['conditions']) ? (array)$element['unique']['conditions'] : array();
		$model = $element['unique']['model'];
		$field = $element['unique']['field'];

		$db = $model::dbObject();
		$params = isset($conditions[1]) ? $conditions[1] : array();
		$conditions = $db->replaceholders($conditions[0], $params);
		$conditions .= ' AND '.$db->stringifyConditions(array($field => $this->input($name)));

		$exists = $model::count($conditions);
		return !$exists;
	}

	public function validateCSV( $form, $name ) {
		$value = trim($this->input($name, ''));
		return 0 < preg_match('/^[a-z ]+(?:, ?[a-z ]+)*$/', $value);
	}

	public function validateNumber( $form, $name ) {
		return is_numeric($this->input($name));
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
				$fields = !isset($element['fields']) ? array($element['_name']) : (array)$element['fields'];
				foreach ( $fields AS &$name ) {
					$name = $this->_elements[$name]['title'];
					unset($name);
				}
				return isset($element['message']) ? $element['message'] : 'Validation failed for: "'.implode('", "', $fields).'"';
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
		$elements = $this->useElements();
		$element = $elements[$name];

		// check input (probably POST) data
		if ( isset($this->input[$name]) ) {
			return $this->input[$name];
		}

		// check default form values
		$dv = (array)$this->default;
		if ( isset($dv[$name]) ) {
			return $dv[$name];
		}

		// check default element value
		if ( isset($element['default']) ) {
			return $element['default'];
		}

		// no input found: return alt
		return $alt;
	}

	public function output( $name, $value = null ) {
		if ( 1 < func_num_args() ) {
			return $this->output[$name] = $value;
		}
		return isset($this->output[$name]) ? $this->output[$name] : '';
	}

	public function splitOutput( $lists ) {
		$output = array();
		foreach ( $lists AS $listName => $fields ) {
			$output[$listName] = array();
			is_array($fields) or $fields = explode(',', $fields);
			foreach ( $fields AS $fieldName ) {
				if ( array_key_exists($fieldName, $this->output) ) {
					$output[$listName][$fieldName] = $this->output[$fieldName];
				}
			}
		}
		return $output;
	}



	public function renderGridElement( $name, $element, $wrapper = true ) {
		$html = "\n".'<table class="grid">'."\n";
		$html .= '	<tr>'."\n";
		$html .= '		<th class="corner"></th>'."\n";
		foreach ( $element['horizontal'][1] AS $k => $hLabel ) {
			$html .= '		'.$this->renderGridHorizontalTH($element, $hLabel)."\n";
		}
		$html .= '	</tr>'."\n";
		foreach ( $element['vertical'][1] AS $vKey => $vLabel ) {
			$vKey = $this->getOptionValue($vKey, $vLabel);
			$html .= '	<tr>'."\n";
			$html .= '		'.$this->renderGridVerticalTH($element, $vLabel)."\n";
			foreach ( $element['horizontal'][1] AS $hKey => $hLabel ) {
				$hKey = $this->getOptionValue($hKey, $hLabel);
				$sub = '?';
				$fn = 'renderGrid'.$element['subtype'];
				if ( is_callable($method = array($this, $fn)) ) {
					list($xValue, $yValue) = empty($element['reverse']) ? array($vKey, $hKey) : array($hKey, $vKey);
//					$sub = $this->$fn($name.'__'.$xValue.'[]', $yValue);
					$sub = $this->$fn($element, $xValue, $yValue);
				}
				$html .= '		<td>'.$sub.'</td>'."\n";
			}
			$html .= '	</tr>'."\n";
		}
		$html .= '</table>'."\n";

		if ( !$wrapper ) {
			return $html;
		}

		return $this->renderElementWrapperWithTitle($html, $element);
	}

	protected function renderGridHorizontalTH( $element, $label ) {
		$html = '<th class="horizontal">'.$label.'</th>';
		return $html;
	}

	protected function renderGridVerticalTH( $element, $label ) {
		$html = '<th class="vertical">'.$label.'</th>';
		return $html;
	}

	protected function renderGridOptions( $element, $xValue, $yValue ) {
		$name = $element['_name'];
		$options = $element['options'];
		$dummy = isset($element['dummy']) ? $element['dummy'] : '';

		$input = $this->input($name, array());
		$value = isset($input[$xValue][$yValue]) ? $input[$xValue][$yValue] : '';

		$elName = $name."[$xValue][$yValue]";
		$html = $this->renderSelect($elName, $options, $value, $dummy);

		return $html;
	}

	protected function renderGridCheckbox( $element, $xValue, $yValue ) {
		$name = $element['_name'];

		$input = $this->input($name, array());
		$value = isset($input[$xValue]) ? (array)$input[$xValue] : array();
		$checked = in_array($yValue, $value) ? ' checked' : '';

		$html = '<input type="checkbox" name="'.$name.'['.$xValue.'][]" value="'.$yValue.'"'.$checked.' />';

		return $html;
	}

/*	public function renderGridCheckboxElement( $elementName, $elementValue ) {
		$formValue = $this->input(trim($elementName, ']['), array());
		$checked = in_array($elementValue, $formValue) ? ' checked' : '';
		$html = '<input type="checkbox" name="'.$elementName.'" value="'.$elementValue.'"'.$checked.' />';

		return $html;
	}*/


	public function renderRadioElement( $name, $element, $wrapper = true ) {
		$type = $element['type'];
		$elName = $name;
		$checked = $this->input($name, null);

		$options = array();
		foreach ( (array)$element['options'] AS $k => $v ) {
			$k = $this->getOptionValue($k, $v);
			$options[] = '<span class="option"><label><input type="radio" name="'.$elName.'" value="'.$k.'"'.( $checked === $k ? ' checked' : '' ).' /> '.$v.'</label></span>';
		}
		$html = implode(' ', $options);

		if ( !$wrapper ) {
			return $html;
		}

		return $this->renderElementWrapperWithTitle($html, $element);
	}

	public function renderCheckboxElement( $name, $element, $wrapper = true ) {
		$elName = $name;
		$checked = null !== $this->input($name, null) ? ' checked' : '';
		$value = isset($element['value']) ? ' value="'.Output::html($element['value']).'"' : '';

		$input = '<label><input type="checkbox" name="'.$elName.'"'.$value.$checked.' /> '.$element['title'].'</label>';
		$html = '<span class="input">'.$input.'</span>';
		if ( !empty($element['description']) ) {
			$html .= ' ';
			$html .= '<span class="description">'.$element['description'].'</span>';
		}

		if ( !$wrapper ) {
			return $html;
		}

		return $this->renderElementWrapper($html, $element);
	}

	public function renderCheckboxesElement( $name, $element, $wrapper = true ) {
		$elName = $name.'[]';
		$checked = (array)$this->input($name, array());

		$options = array();
		foreach ( $element['options'] AS $k => $v ) {
			$k = $this->getOptionValue($k, $v);
			$options[] = '<span class="option"><label><input type="checkbox" name="'.$elName.'" value="'.$k.'"'.( in_array($k, $checked) ? ' checked' : '' ).' /> '.$v.'</label></span>';
		}
		$html = implode(' ', $options);

		if ( !$wrapper ) {
			return $html;
		}

		return $this->renderElementWrapperWithTitle($html, $element);
	}


	public function renderDropdownElement( $name, $element, $wrapper = true ) {
		return $this->renderOptionsElement($name, $element, $wrapper);
	}

	public function renderOptionsElement( $name, $element, $wrapper = true ) {
		$value = $this->input($name, 0);
		$elName = $name;

		$dummy = isset($element['dummy']) ? $element['dummy'] : '';
		$html = $this->renderSelect($elName, $element['options'], $value, $dummy);

		if ( !$wrapper ) {
			return $html;
		}

		return $this->renderElementWrapperWithTitle($html, $element);
	}

	protected function renderSelect( $elName, $options, $value = '', $dummy = '' ) {
		$html = '<select name="'.$elName.'">';
		if ( $dummy ) {
			$html .= '<option value="'.$this->getDummyOptionValue().'">'.$dummy.'</option>';
		}
		foreach ( (array)$options AS $k => $v ) {
			$k = $this->getOptionValue($k, $v);
			$html .= '<option value="'.$k.'"'.( (string)$k === $value ? ' selected' : '' ).'>'.$v.'</option>';
		}
		$html .= '</select>';
		return $html;
	}

	protected function getDummyOptionValue( $element = null ) {
		return '';
	}

	protected function getOptionValue( $k, $v ) {
		if ( is_a($v, '\row\database\Model') ) {
			$k = implode(',', $v->_pkValue());
		}
		return $k;
	}

	public function renderTextElement( $name, $element, $wrapper = true ) {
		$type = $element['type'];
		$elName = $name;
		$value = $this->input($name);

		$html = '<input type="'.$type.'" name="'.$elName.'" value="'.$value.'" />';

		if ( !$wrapper ) {
			return $html;
		}

		return $this->renderElementWrapperWithTitle($html, $element);
	}

	public function renderTextareaElement( $name, $element, $wrapper = true ) {
		$value = $this->input($name);
		$elName = $name;

		$options = Options::make($element);

		$rows = $options->rows ? ' rows="'.$options->rows.'"' : '';
		$cols = $options->cols ? ' cols="'.$options->cols.'"' : '';

		$html = '<textarea'.$rows.$cols.' name="'.$elName.'">'.$value.'</textarea>';

		if ( !$wrapper ) {
			return $html;
		}

		return $this->renderElementWrapperWithTitle($html, $element);
	}

	public function renderMarkupElement( $name, $element ) {
		$options = Options::make(Options::make($element));

		$inside = $options->inside ?: $options->text;

		if ( $inside ) {
			return '<'.$this->elementWrapperTag.' class="form-element markup '.$name.'">'.$inside.'</'.$this->elementWrapperTag.'>';
		}
		else if ( $options->outside ) {
			return $options->outside;
		}

		return '';
	}



	public function &useElements() {
		if ( !$this->_elements ) {
			$elements = array();
			$index = 0;
			foreach ( $this->elements($this->default) AS $name => $element ) {
				$element['_name'] = $name;
				$element['_index'] = $index++;
				$this->elementTitle($element);
				$elements[$name] = $element;
			}
			$this->_elements = $elements;
		}
		return $this->_elements;
	}

	public function render( $withForm = true, $options = array() ) {
		$elements = $this->useElements();

		// Render 1 element?
		if ( is_string($withForm) && isset($this->_elements[$withForm]) ) {
			// First argument is element name, so render only that element
			return $this->renderElement($withForm, $this->_elements[$withForm]);
		}

		$index = 0;
		$html = '';
		foreach ( $elements AS $name => $element ) {
			if ( is_string($name) || ( isset($element['type']) && in_array($element['type'], array('markup')) ) ) {
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
		if ( isset($this->renderers[$name]) && ( is_callable($fn = $this->renderers[$name]) || \row\core\is_callable($fn = array($this, (string)$this->renderers[$name])) ) ) {
			// Unfortunately, the second is_callable always returns true, so the next line might throw a MethodException
			return call_user_func($fn, $name, $element, $this);
		}

		if ( empty($element['type']) ) {
			return '';
		}
		$type = $element['type'];
		$renderFunction = 'render'.ucfirst($type).'Element';
		$renderMethod = array($this, $renderFunction);
		if ( !empty($element['render']) ) {
			$renderMethod = $element['render'];
		}
		if ( is_callable($renderMethod) ) {
			return call_user_func($renderMethod, $name, $element);
		}

		return $this->renderTextElement($name, $element);
	}

	public function elementName( $element ) {
		$name = $element['_name'];
		if ( isset($element['name']) ) {
			$name = $element['name'];
		}
		else if ( isset($element['type']) && in_array($element['type'], array('checkboxes')) ) {
			$name .= '[]';
		}
		return $name;
	}

	public function renderButtons() {
		return '<'.$this->elementWrapperTag.' class="form-submit"><input type=submit></'.$this->elementWrapperTag.'>';
	}

	public function renderElementWrapper( $html, $element ) {
		$name = $element['_name'];
		return '<'.$this->elementWrapperTag.' class="form-element '.$element['type'].' '.$name.$this->error($name).'">'.$html.'</'.$this->elementWrapperTag.'>';
	}

	public function renderElementWrapperWithTitle( $input, $element ) {
		$name = $element['_name'];

		$description = empty($element['description']) ? '' : '<span class="description">'.$element['description'].'</span>';
		$error = $this->inlineErrors && isset($this->errors[$name]) ? '<span class="error">'.$this->errors[$name][0].'</span>' : '';

		$html = '<label>'.$element['title'].'</label><span class="input">'.$input.'</span>'.$error.$description;

		return $this->renderElementWrapper($html, $element);
	}

	public function elementSeparator() {
		return "\n\n";
	}



	public function elementTitle( &$element ) {
		if ( empty($element['title']) ) {
			$element['title'] = $this->nameToTitle($element['_name']);
		}
	}

	public function nameToTitle( $name ) {
		return Inflector::spacify($name); // 'beautify'
	}



	public function __tostring() {
		return $this->render();
	}


}


