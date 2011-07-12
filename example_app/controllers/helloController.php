<?php

namespace app\controllers;

use app\specs\Controller;

class helloController extends Controller {

	public function _pre_action() {
		echo '<pre>';
	}

	public function _post_action() {
		echo '</pre>';
	}

	public function dave( $ip, $port = '17494' ) {

		require(__DIR__.'/inc.cls.ethrly.php');
		$dave = new \ETHRLY($ip, $port, 2);

		if ( !$dave->socket() ) {
			var_dump($dave->error);
			exit;
		}

		// status
		$status = $dave->status();
		echo "status: ".implode($status)."\n\n";

		// all off
		$dave->off();
		echo "all off\n\n";

		// status
		$status = $dave->status();
		echo "status: ".implode($status)."\n\n";

		// 3 relays on
		$relays = array_rand(array_flip(range(1, 8)), 3);
		$dave->on($relays);
		echo "on: ".implode(', ', $relays)."\n\n";

		// status
		$status = $dave->status();
		echo "status: ".implode($status)."\n\n";
	}

	public function world() {
		echo 'Hello, world!';
	}

	public function args( $id, $oele = 'x' ) {
		print_r(func_get_args());
		print_r($_GET);
	}

}


