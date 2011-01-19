<?php

namespace row\http;

use row\core\Object;
use row\utils\Options;
//use row\core\Vendors;

class NotFoundException extends \RowException { }

class Dispatcher extends Object {

	public function __tostring() {
		return 'Dispatcher';
	}

	public $requestPath = false; // false means unset - will become a (might-be-empty) string
	public $requestBasePath = '';

	public $options; // typeof Options

	public function __construct( $options = array() ) {
		$defaults = Options::make(array(
			'module_delim' => '-',
			'default_module' => 'index',
			'default_action' => 'index',
			'dispatch_order' => array('specific', 'generic', 'fallback'),
			'not_found_exception' => 'row\http\NotFoundException',
			'module_class_prefix' => '',
			'module_class_postfix' => 'Controller',
			'action_name_translation' => function($action) {
				return str_replace('-', '_', $action);
			},
			'action_path_wildcards' => Options::make(array(
				'INT'		=> '(\d+)',
				'STRING'	=> '([^/]+)',
				'DATE'		=> '(\d\d\d\d\-\d\d?\-\d\d?)',
				'ANYTHING'	=> '(.+)',
				'VERSION'	=> '(\d+\.\d+\.\d+\)',
			)),
			'action_path_wildcard_aliases' => Options::make(array(
				'#' => 'INT',
				'*' => 'STRING',
			)),
			'ignore_leading_slash' => false,
			'ignore_trailing_slash' => false,
			'case_sensitive_paths' => false,
			// Unimplemented:
//			'path_source' => 'REQUEST_URI', // REQUEST_URI | INDEX_PHP | QUERY_STRING | GET
//			'path_source_get_param' => 'url',
		));
		$this->options = new Options($options, $defaults);
		$this->_init();
	}


	public function _init() {
		$this->getRequestPath();
		// Anything else? Anything??
	}


	public function setRouter( \row\http\Router $router ) {
		$this->router = $router;
	}


	public function getRequestPath() {
		if ( false === $this->requestPath ) {
			$uri = explode('?', $_SERVER['REQUEST_URI'], 2);
			if ( isset($uri[1]) ) {
				parse_str($uri[1], $_GET);
			}
			$path = $uri[0];
//print_r($_SERVER);
			$base = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$this->requestBasePath = $base;
//var_dump($base, $path);
			$path = substr($path, strlen($base));
//var_dump($path);
//exit;
			if ( $this->options->ignore_leading_slash && $this->options->ignore_trailing_slash ) {
				$path = trim($path, '/');
			}
			else if ( $this->options->ignore_leading_slash ) {
				$path = ltrim($path, '/');
			}
			else if ( $this->options->ignore_trailing_slash ) {
				$path = rtrim($path, '/');
			}
			$this->requestPath = $path;
		}
		return $this->requestPath;
	}


	public function getApplication( $f_path ) {
		$application = $this->getController($f_path);
		if ( !is_a($application, 'row\Controller') ) {
			$class = $this->options->not_found_exception;
			throw new $class($f_path);
		}

		// Yuck!
		$application->_dispatcher = $this;
		$this->application = $application;

		return $application;
	}


	public function getController( $f_path ) {
		$uri = explode('/', trim($f_path, '/'), 2);
		$module = $uri[0] ?: $this->options->default_module;
		foreach ( $this->options->dispatch_order AS $dispatchType ) {
			switch ( $dispatchType ) {
				case 'generic':
					if ( isset($uri[1]) ) {
						$args = explode('/', $uri[1]);
						$action = array_shift($args);
						$translate = $this->options->action_name_translation;
						if ( is_callable($translate) ) {
							$action = $translate($action);
						}
					}
					else {
						$action = $this->options->default_action;
						$args = array();
					}

					$this->_module = $module;
					$this->_action = $action;
					$this->_arguments = $args;

					$class = $this->options->module_class_prefix . $module . $this->options->module_class_postfix;
					$namespacedClass = 'app\\controllers\\' . $class;
					$loader = \Vendors::$loaders['app'];
					$file = $loader('app', 'controllers\\'.$class);
					if ( !file_exists($file) ) {
						$class = $this->options->not_found_exception;
						throw new $class($f_path);
					}

					$application = new $namespacedClass( $this );
					$application->_executable = is_callable(array($application, $action));
					return $application;
				break;
			}
		}

		return false;
	}


} // END Class Dispatcher


