<?php

namespace app\controllers;

use app\controllers\ControllerParent;

class todoController extends ControllerParent {

	public function index() {
//		header('Content-type: text/plain');
		$todo = file_get_contents(ROW_PATH.'/TODO.md');
		require(ROW_VENDORS_PATH.'/phpMarkdownExtra/Markdown.php');
		echo Markdown($todo);
		exit;
	}

}


