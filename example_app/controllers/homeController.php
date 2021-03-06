<?php

namespace app\controllers;

use app\specs\Controller;
use Zend_Component_Session;
use row\utils\Image;
use app\models;
use app\specs\Output;

class homeController extends Controller {

	protected $_actions = array(
		'/js/all.js'	=> 'allJS',
		'/oele/#'		=> 'oele',
		'/code'			=> 'code',
		'/form/%'		=> 'form',
		'/blog/*'		=> 'blog',
		'/more'			=> 'more',
		'/more/%'		=> 'more',
	);


	public function oele( $no ) {
		echo '<pre>';
		echo "oele:\n";
		var_dump($no);
	}


	public function error( $type = '' ) {
		var_dump($type);
	}


	public function mobile() { // REST style. Why? No idea. Because we can!
echo "// MOBILE //\n";
		$path = implode('/', func_get_args());
var_dump($path);
		return $this->_internal($path);
	}


	public function code() {
		if ( !isset($_POST['code']) ) {
			$_POST['code'] = 'print_r($_GET);';
		}
		$code = $_POST['code'];

		$db = $GLOBALS['db'];
		echo '<pre>';
		try {
			eval($code);
		}
		catch ( \Exception $ex ) {
			echo "\n=================== exception ====================\n\n";
			print_r($ex);
		}
		echo '</pre><br><br><hr>';

		echo '<form method="post"><textarea rows=20 cols=120 name=code>'.Output::html($code).'</textarea><br><input type=submit></form>';
	}


	public function allJS() {
		// create 1 JS file from several
		$files = array('framework', 'library-2', 'library-3');
		$js = '';
		foreach ( $files AS $file ) {
			$file = ROW_APP_WEB.'/js/'.$file.'.js';
			$js .= trim(file_get_contents($file)).";\n\n";
		}
//		file_put_contents(ROW_APP_WEB.'/js/all.js', $js);
		header('Content-type: application/x-javascript');
		echo "/* PHP generated JS */\n\n".$js;
	}


	public function image() {
		$image = new Image(ROW_VENDOR_ROW_PATH.'/drupal/imagecache/sample.png');
		$image->resize(0, 100);
		$image->output();
	}


	public function brmember() {
		$form = new \app\forms\BRMember($this, array('table' => true));
		if ( $this->POST ) {
			if ( $form->validate($_POST) ) {
				return 'OK';
			}
			return print_r($form->errors(), 1);
		}
		return $this->tpl->display('forms/brmember', get_defined_vars(), '_todoLayout');
	}


	public function form( $form ) {
		$class = 'app\\forms\\'.$form;
		$form = new $class($this, array(
			'user' => models\User::first('1 ORDER BY RAND()'),
			'domain' => models\Domain::first('1 ORDER BY RAND()'),
		));

		$content = '';
		if ( $this->POST ) {
			$content .= '<pre>VALIDATION: ' . ( $form->validate($_POST) ? 'OK' : 'FAIL' ) . '</pre>';
			$content .= '<pre>'.print_r($form->errors(), 1).'</pre>';
		}

		$content .= $form->render();
		$content .= '<pre>$_POST: '.print_r($_POST, 1).'</pre>';
		$content .= '<pre>&output: '.print_r($form->output, 1).'</pre>';
		$content .= '<pre>$form: '.print_r($form, 1).'</pre>';

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

		var_dump($this->dispatcher->cacheClear());
		var_dump(\Vendors::cacheClear());
		echo '<p>Also, I flushed the Vendors cache and Dispatch cache! You\'re welcome!</p>';
	}


	public function cache() {
		echo '<pre>';
		\Vendors::cacheLoad();
		print_r(\Vendors::$cache);

		$this->dispatcher->cacheLoad();
		print_r($this->dispatcher->cache);
		echo '</pre>';
	}


}


