<?php

namespace app\controllers;

use app\specs\Controller;

class helloController extends Controller {

	public function indexAction() {
		$html = '<p>Hello, world!</p>';

		$this->tpl = new \app\specs\Output($this);

		return $this->tpl->display(false, array('content' => $html), true);
	}

}


