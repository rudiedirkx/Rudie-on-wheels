<?php

namespace app\controllers;

use app\specs\Controller;
use row\auth\Session;
use app\specs\Output;

class todoController extends Controller {

	protected function _pre_action() {
		echo '<!doctype html><head><style>body{font-family:Arial,sans-serif;}code{white-space:nowrap;padding:3px;background-color:#e4e4e4;}</style></head><body>'."\n\n";
	}

	protected function _post_action() {
		echo "\n\n".'</body></html>';
	}

	public function issue( $n = 0 ) {
		echo 'Show TODO issue # '.$n.' here...';
	}

	public function readme() {
		return $this->index('README.md');
	}

	public function index( $file = 'TODO.md' ) {
//		Session::success('You did the right thing coming here =)');
//		print_r(Session::messages());

		$todo = file_get_contents(ROW_PATH.'/'.$file);
		$todo = Output::markdown($todo);
		echo $todo;
	}

}


