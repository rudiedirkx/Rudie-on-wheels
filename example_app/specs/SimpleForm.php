<?php

namespace app\specs;

class SimpleForm extends \row\form\SimpleForm {

	function renderDateElement( $name, $element ) {
		$html = '<input name="'.$name.'" value="'.$this->input($name).'" /> <img src="'.Output::url('images/calendar.png').'" onclick="alert(\'open datepicker\');" />';

		return $this->renderElementWrapper($html, $element);
	}

}


