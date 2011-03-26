<?php

namespace row\form;

use row\core\Options;
use row\utils\Inflector;
use row\Output;

class SimpleForm extends \row\Component {

	public $input = array();

	public $output = array();


	public function input( $name ) {
		return isset($this->input[$name]) ? $this->input[$name] : '';
	}

	public function output( $name, $value = null ) {
		if ( null !== $value ) {
			return $this->output[$name] = $value;
		}
		return isset($this->output[$name]) ? $this->output[$name] : '';
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
			$element['name'] = $name;
			$element['index'] = $index++;
			$html .= $this->renderElement($name, $element);
			$html .= $this->elementSeparator();
		}
		if ( $withForm ) {
			$options = Options::make($options);
			$method = $options->get('action', 'post');
			$action = Output::url($this->application->_uri);
			$html = '<form method="'.$method.'" action="'.$action.'">' .
					$this->elementSeparator() .
					$html.$this->renderButtons() .
					$this->elementSeparator() .
					'</form>';
		}
		return $html;
	}

	public function renderElement( $name, $element ) {
		if ( empty($element['title']) ) {
			$element['title'] = $this->nameToTitle($name);
		}

		$type = $element['type'];
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
		return '<p class="form-element '.$element['type'].' '.$name.'"><label>'.$element['title'].'</label><span class="input">'.$input.'</span></p>';
	}

	public function elementSeparator() {
		return "\n\n";
	}



	public function nameToTitle( $name ) {
		return Inflector::spacify($name); // 'beautify'
	}



	public function __tostring() {
		return $this->render();
	}


}


