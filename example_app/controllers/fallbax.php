<?php

namespace app\controllers;

use app\specs\Controller;

class fallbax extends Controller {

	protected function _init() {
		parent::_init();
//echo "\n<h1>:: EXECUTED ".__METHOD__." ::</h1>\n\n";
	}

	public function blog() {
		echo '<p>You are here because you are on an INVALID BLOG URI...</p>';
	}

	public function more( $fallback = '?' ) {
		echo '<p>More what?</p>';
		echo '<pre>';
		var_dump($fallback);
		echo '</pre>';
	}

	public function flush() {
		echo '<p>This is in the fallback module. Kewl =)</p>';

		echo '<pre>';
		var_dump(__METHOD__);
		echo '</pre>';
		echo '<pre>';
		print_r(func_get_args());
		echo '</pre>';

		\Vendors::cacheClear();
		echo '<p>Also, I flushed the Vendors cache! You\'re welcome!</p>';
	}

	public function cache() {
		echo '<pre>';
		\Vendors::cacheLoad();
		print_r(\Vendors::$cache);
		echo '</pre>';
	}

}


