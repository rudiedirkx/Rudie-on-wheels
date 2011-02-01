<?php

namespace app\specs;

/**
 * The row\http\Dispatcher is a good thing to extend
 * because you can change default configuration in here.
 * 
 * In this case I don't, because I like my configuration
 * easily accessibly/changeable in index.php.
 * The easiest way to alter default configuration is to
 * overwrite `getDefaultOptions()` and put YOUR default options
 * there. Not-default options are then still possible in index.php.
 * 
 * If you do extend the default Dispatcher, don't forget to
 * use THAT Dispatcher (app\specs\Dispatcher instead of
 * row\http\Dispatcher) in index.php
 */

class Dispatcher extends \row\http\Dispatcher {

	/**
	 * Just an example of extending the default options:
	 *
	static public function getDefaultOptions() {
		$options = parent::getDefaultOptions();
		$options->module_delim = false;
		$options->default_module = 'home'; // If you don't like "index"
		$options->default_action = 'controllerIndex'; // If you don't like "index"
	} // */

}


