<?php

namespace app\controllers;

use app\controllers\ControllerParent;

class todoController extends ControllerParent {

	public function _pre_action() {
		echo '<!doctype html><head><style>body{font-family:Arial,sans-serif;}code{padding:3px;background-color:#e4e4e4;}</style></head><body>'."\n\n";
	}

	public function _post_action() {
		echo "\n\n".'</body></html>';
	}

	public function index() {
		$todo = file_get_contents(ROW_PATH.'/TODO.md');
		$todo = \markdown\MarkdownParser::parse($todo);
		echo $todo;
	}

}


