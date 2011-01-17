<?php

namespace app\controllers;

use row\Controller;

class indexController extends Controller {

	public function __construct() {
		$this->redirect('/blog');
	}

}


