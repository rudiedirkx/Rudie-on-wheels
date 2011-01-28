<?php

namespace app\controllers;

use app\specs\Controller;
use row\auth\Session;

class sessionsController extends Controller {

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


