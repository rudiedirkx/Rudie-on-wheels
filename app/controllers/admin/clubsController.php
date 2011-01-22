<?php

namespace app\controllers\admin;

class clubsController extends \app\controllers\ControllerParent {

	public function index() {
		echo '<pre>Home of '.__METHOD__."\n";
		print_r($this->_arguments);
	}

}


