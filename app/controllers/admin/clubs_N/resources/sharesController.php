<?php

namespace app\controllers\admin\clubs_N\resources;

class sharesController extends \app\controllers\ControllerParent {

	public function index() {
		echo '<pre>Home of '.__METHOD__."\n";
		print_r($this->_arguments);
	}

}


