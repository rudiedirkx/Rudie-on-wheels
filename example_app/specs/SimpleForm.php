<?php

namespace app\specs;

abstract class SimpleForm extends \row\form\SimpleForm {

	function renderDateElement( $name, $element ) {
		$html = '<input class="date" name="'.$name.'" value="'.$this->input($name).'" /> <img class="date" src="'.Output::url('images/calendar.png').'" />';

		return $this->renderElementWrapperWithTitle($html, $element);
	}

}


