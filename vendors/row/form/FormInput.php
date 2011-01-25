<?php

namespace row\form;

use row\core\Object;

abstract class FormInput extends Object {

	public $title = '';

	public function __construct( $title ) {
		$this->title = $title;
	}

	abstract public function render();

}


