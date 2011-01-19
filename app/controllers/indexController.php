<?php

namespace app\controllers;

use row\Controller;

class indexController extends Controller {

	public function _init() {
		$this->redirect('/blog');
	}

}


