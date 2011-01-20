<?php

namespace app\controllers;

use row\Controller;
use row\auth\SessionUser;
use row\View;

class ControllerParent extends Controller {

	public function _init() {
		$this->user = new SessionUser;
		$this->tpl = new View($this);
		$this->tpl->viewsFolder = ROW_APP_PATH.'/views';
		$this->db = $GLOBALS['db'];
	}

}


