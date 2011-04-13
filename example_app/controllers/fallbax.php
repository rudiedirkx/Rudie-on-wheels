<?php

namespace app\controllers;

use Zend_Component_Session;

class fallbax extends \app\specs\Controller {

	/* this _init exists solely for debugging purposes *
	protected function _init() {
		parent::_init();
//echo "\n<h1>:: EXECUTED ".__METHOD__." ::</h1>\n\n";
	}
	/**/

	function zend() {
		echo "<pre>\n";
		echo "doing Zend shizzle here...\n\n";

		$zendSession = new Zend_Component_Session;
		var_dump($zendSession);

		$zendUser = $zendSession->user();
		var_dump($zendUser);

		$zendACL = $zendSession->acl();
		var_dump($zendACL);
	}

	public function blog() {
		echo '<p>You are here because you are on an INVALID BLOG URI...</p>';
	}

	public function more( $path = '?' ) {
		echo '<p>More what?</p>';
		echo '<pre>';
		var_dump($path);
		echo '</pre>';
	}

	public function flush_apc_cache() {
		return $this->flush();
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


