<?php

namespace app\controllers;

use Zend_Component_Session;
use row\utils\Image;

class fallbax extends \app\specs\Controller {

	public function image() {
//echo '<pre>';
		$image = new Image(ROW_VENDOR_ROW_PATH.'/drupal/imagecache/sample.png');
//print_r($image);
		$image->resize(0, 100);
//print_r($image);
		$image->output();
	}

	public function form( $form ) {
		$class = 'app\\forms\\'.$form;
		$form = new $class($this);
		$content = '';
		if ( self::post() ) {
			$form->validate($_POST);
			$content .= '<pre>'.print_r($form->errors(), 1).'</pre>';
		}
		$content .= $form->render();
		return $this->tpl->display(false, array('content' => $content));
	}

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


