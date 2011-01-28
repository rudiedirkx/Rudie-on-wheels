<?php

namespace app\controllers;

use app\specs\Controller;

class adminController extends Controller {

	public function index() {
		echo '<pre>Home of '.__METHOD__."\n";
		print_r($this->_arguments);
	}

}


