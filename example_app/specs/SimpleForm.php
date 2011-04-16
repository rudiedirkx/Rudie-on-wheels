<?php

namespace app\specs;

abstract class SimpleForm extends \row\form\SimpleForm {

	function renderDateElement( $name, $element ) {
		$html = '<input name="'.$name.'" value="'.$this->input($name).'" /> <img src="'.Output::url('images/calendar.png').'" onclick="var e=this.previousSibling.previousSibling,d=prompt(\'Fill in a date with format YYYY-MM-DD\', e.value);if(d){e.value=d;}" />';

		return $this->renderElementWrapperWithTitle($html, $element);
	}

}


