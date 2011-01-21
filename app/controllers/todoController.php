<?php

namespace app\controllers;

use app\controllers\ControllerParent;

class todoController extends ControllerParent {

	protected function _pre_action() {
		echo '<!doctype html><head><style>body{font-family:Arial,sans-serif;}code{padding:3px;background-color:#e4e4e4;}</style></head><body>'."\n\n";
	}

	protected function _post_action() {
		echo "\n\n".'</body></html>';
	}

	public function issue( $n = 0 ) {
		echo 'Show TODO issue # '.$n.' here...';
	}

	public function index() {
		$todo = file_get_contents(ROW_PATH.'/TODO.md');
		$todo = \markdown\MarkdownParser::parse($todo);
		echo $todo;
	}

}


