<?php

namespace app\controllers;

use app\specs\Controller;

class adminController extends Controller {

	public function index() {
		echo '<pre>@ '.__METHOD__."\n";
		print_r($this->_arguments);
	}

	public function more( $arg1 = 0 ) {
		echo '<pre>@ '.__METHOD__."\n";
		print_r(func_get_args());
	}

}


