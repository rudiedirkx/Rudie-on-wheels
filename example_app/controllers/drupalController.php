<?php

namespace app\controllers;

class drupalController extends dbController {

	public function index( $module = '' ) {
		$drupalDir = ROW_VENDOR_ROW_PATH.'/drupal';

		// drupal bootstrap
//		set_include_path($drupalDir.'/core/'.PATH_SEPARATOR.get_include_path());
		chdir($drupalDir.'/core');
		require_once $drupalDir.'/core/includes/bootstrap.inc';
		require_once $drupalDir.'/core/includes/common.inc';
		require_once $drupalDir.'/core/includes/file.inc';
//		drupal_bootstrap(DRUPAL_BOOTSTRAP_CONFIGURATION);

		// using module 'imagecache'...
echo '<pre>';
		foreach ( array($drupalDir.'/imagecache', /*$drupalDir.'/imagecache_actions'*/) AS $dir ) {
			foreach ( glob($dir.'/*.*') AS $file ) {
				if ( in_array(substr($file, strrpos($file, '.')), array('.module', '.php', '.inc', '.class')) ) {
					require_once($file);
				}
			}
		}
		$icbd = imagecache_build_derivative(array(), $drupalDir.'/imagecache/sample.png', __DIR__.'/sample-out.png');
		var_dump($icbd);
	}

}


