<?php

namespace app\controllers;

use app\specs\Controller;

class errorsController extends Controller {

	public function notfound() {
		$exception = $this->_dispatcher->params->exception;

		return get_defined_vars();
	}

}


