<?php

namespace app\controllers\admin\clubs_N\resources_N;

use app\specs\Controller;

class shares_NController extends Controller {

	public function toggle() {
		echo '<pre>Home of '.__METHOD__."\n";
		echo "\nModule arguments:\n";
		print_r($this->_arguments);
		echo "\nAction arguments:\n";
		print_r(func_get_args());
	}

	public function index() {
		echo '<pre>Home of '.__METHOD__."\n";
		print_r($this->_arguments);
	}

}


