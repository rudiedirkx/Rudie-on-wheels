<?php

namespace app\controllers;

use row\Controller;
use app\specs\SessionUser;
use row\View;

class ControllerParent extends Controller {

	public function _init() {
		$this->user = new SessionUser;
		$this->tpl = new View($this);
		$this->tpl->viewsFolder = ROW_APP_PATH.'/views';
		$this->tpl->viewLayout = ROW_APP_PATH.'/views/_layout.php';
		$this->db = $GLOBALS['db'];
	}

}


