<?php

namespace app\controllers;

use app\controllers\ControllerParent;
use row\auth\Session;

class sessionsController extends ControllerParent {

	public function get( $key ) {
		echo '<pre>';
		var_dump(Session::variable($key));
		print_r($_SESSION);
	}

	public function set( $key, $val ) {
		echo '<pre>';
		var_dump(Session::variable($key, $val));
		print_r($_SESSION);
	}

}


