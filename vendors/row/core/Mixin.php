<?php

namespace row\core;

abstract class Mixin extends Object {

	public $object;

	public function __construct( $object ) {
		$this->object = $object;
		$this->_fire('init');
	}

	protected function _init() {}

}


