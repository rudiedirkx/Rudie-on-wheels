<?php

namespace app\controllers;

class adminController extends \app\controllers\ControllerParent {

	public function index() {
		echo '<pre>Home of '.__METHOD__."\n";
		print_r($this->_arguments);
	}

}


