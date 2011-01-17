<?php

namespace app\controllers;

use row\Controller;
use row\auth\SessionUser;
use row\View;

class ControllerParent extends Controller {

	public function __construct( $action, $args ) {
		parent::__construct($action, $args);
		$this->user = new SessionUser;
		$this->tpl = new View($this);
		$this->tpl->viewsfolder = ROW_APP_PATH.'/views';
	}

}


