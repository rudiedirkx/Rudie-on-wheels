<?php

namespace app\controllers;

use app\specs\Controller;
use row\auth\Session;
use app\specs\Output;

class todoController extends Controller {

	protected function _init() {
		parent::_init();
		$this->tpl->viewLayout = '_todoLayout';
	}

	public function issue( $n = 0 ) {
		echo 'Show TODO issue # '.$n.' here...';
	}

	public function readme() {
		return $this->index('README.md');
	}

	public function install() {
		return $this->index('INSTALL.md');
	}

	public function index( $file = 'TODO.md' ) {
		$todo = file_get_contents(ROW_PATH.'/'.$file);
		$todo = Output::markdown($todo);

		$this->tpl->display(false, array('content' => $todo)); // Show no View, only Layout
	}

}


