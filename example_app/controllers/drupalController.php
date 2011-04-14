<?php

namespace app\controllers;

class drupalController extends \app\specs\Controller {

	public $drupalDir = '';

	protected function _pre_action() {
		$this->drupalDir = ROW_VENDOR_ROW_PATH.'/drupal';

		// drupal bootstrap
//		set_include_path($drupalDir.'/core/'.PATH_SEPARATOR.get_include_path());
		chdir($this->drupalDir.'/core');
		require_once $this->drupalDir.'/core/includes/bootstrap.inc';
		require_once $this->drupalDir.'/core/includes/common.inc';
		require_once $this->drupalDir.'/core/includes/file.inc';
//		drupal_bootstrap(DRUPAL_BOOTSTRAP_CONFIGURATION);
	}

	public function index( $module = '' ) {
		// using module 'imagecache'...
echo '<pre>';
		foreach ( array($this->drupalDir.'/imagecache', /*$this->drupalDir.'/imagecache_actions'*/) AS $dir ) {
			foreach ( glob($dir.'/*.*') AS $file ) {
				if ( in_array(substr($file, strrpos($file, '.')), array('.module', '.php', '.inc', '.class')) ) {
					require_once($file);
				}
			}
		}
		$icbd = imagecache_build_derivative(array(), $this->drupalDir.'/imagecache/sample.png', __DIR__.'/sample-out.png');
		var_dump($icbd);
	}

}


