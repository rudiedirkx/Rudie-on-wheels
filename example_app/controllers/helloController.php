<?php

namespace app\controllers;

use app\specs\Controller;

class helloController extends \row\YiiController {

	public function _pre_action() {
		echo '<pre>';
	}

	public function _post_action() {
		echo '</pre>';
	}

	public function world() {
		echo 'Hello, world!';
	}

	public function args( $id, $oele = 'x' ) {
		print_r(func_get_args());
		print_r($_GET);
	}

}


