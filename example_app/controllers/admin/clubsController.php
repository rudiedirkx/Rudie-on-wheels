<?php

namespace app\controllers\admin;

use app\specs\Controller;

class clubsController extends Controller {

	public function index() {
		echo '<pre>Home of '.__METHOD__."\n";
		print_r($this->_arguments);
	}

}


