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

	public $cache = false;

	/**
	 * Just an example of extending the default options:
	 *
	public function getDefaultOptions() {
		$options = parent::getDefaultOptions();
		$options->module_delim = false; // Don't check for multi-level Controllers
		$options->default_module = 'home'; // If you don't like "index"
		$options->default_action = 'controllerIndex'; // If you don't like "index"
		return $options;
	} // */

	/**
	 * And it's probably a smart thing to extend the very
	 * minimal, standard exception catch.
	 */
	public function caught( $ex ) {
		$class = get_class($ex);
		switch ( $class ) {
			case 'row\database\ModelException':
			case 'row\database\NotEnoughFoundException':
			case 'row\database\TooManyFoundException':
//				exit('[Database Model error] '.$ex->getMessage().'');
			case 'row\http\NotFoundException':
			case 'row\OutputException':
				return $this->_internal('errors/notfound', array('exception' => $ex));
//				exit('[404] ['.$class.'] Not Found: '.$ex->getMessage());
			case 'row\core\MethodException':
			case 'ErrorException':
				exit('Parse/runtime error: '.$ex->getMessage().'');
			case 'row\database\DatabaseException':
				exit('[Database/Query error] '.$ex->getMessage().'');
		}

		exit('Unknown ['.get_class($ex).'] encountered: '.$ex->getMessage().'');
	}

}


