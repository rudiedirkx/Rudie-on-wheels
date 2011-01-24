<?php

namespace row\http;

use row\core\Object;
use row\utils\Options;
//use row\core\Vendors;

class NotFoundException extends \RowException { } // Where to put this?

class Dispatcher extends Object {

	public function __tostring() {
		return 'Dispatcher';
	}

	public $requestPath = false; // false means unset - will become a (might-be-empty) string
	public $requestBasePath = ''; // The path up to the application (e.g.: /admin/ or /row/)

	public $_module = '';
	public $_moduleArguments = array(); // 'Not yet implemented'
	public $_actionPath = '';
	public $_actionFunction = '';
	public $_actionArguments = array();

	public $router; // typeof row\http\Router

	public $options; // typeof row\core\Options

	static public function getDefaultOptions() { // Easily extendable without altering anything else
		return Options::make(array(
			'module_delim' => '-',
			'default_module' => 'index',
			'default_action' => 'index',
//			'dispatch_order' => array('specific', 'generic', 'fallback'), // This doesn't exist anymore?
			'not_found_exception' => 'row\http\NotFoundException',
			'module_class_prefix' => '',
			'module_class_postfix' => 'Controller',
			'module_to_class_translation' => false,
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
			'ignore_trailing_slash' => false,
			'case_sensitive_paths' => false,
			// Unimplemented:
//			'path_source' => 'REQUEST_URI', // REQUEST_URI | INDEX_PHP | QUERY_STRING | GET
//			'path_source_get_param' => 'url',
		));
	}

	public function __construct( $options = array() ) {
		$defaults = static::getDefaultOptions();
		$this->options = new Options($options, $defaults);
		$this->_init();
	}


	public function _init() {
		$this->getRequestPath();
		// Anything else? Anything??
	}


	public function setRouter( \row\http\Router $router ) {
		$router->setDispatcher($this); // Now the Router knows Dispatcher config like action_path_wildcards
		$this->router = $router;
	}


	public function getRequestPath() {
		if ( false === $this->requestPath ) {
			$uri = explode('?', $_SERVER['REQUEST_URI'], 2);
			if ( isset($uri[1]) ) {
				parse_str($uri[1], $_GET);
			}
			$path = $uri[0];
			$base = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$this->requestBasePath = $base;
			$path = substr($path, strlen($base));
			if ( $this->options->ignore_trailing_slash ) {
				$path = rtrim($path, '/');
			}
			$this->requestPath = $path ?: '/';
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


	public function getController( $path, $routes = true ) {
		$originalPath = $path;
		$this->_module = reset(explode('/', ltrim($path, '/')));
//var_dump($this->_module);
//echo '<pre>';
		if ( $routes && $this->router ) {
			$path != '' || $path = '/';
			if ( $to = $this->router->resolve($path) ) {
				if ( is_array($to) && isset($to['controller'], $to['action']) ) { // Don't evaluate URI like 'normal'
//					$this->_module = $to['controller'];
					$application = $this->getControllerObject($to['controller']);
					$application->_fire('init');
					if ( !$this->isCallableActionFunction($application, $to['action']) ) {
						return $this->throwNotFound();
					}
					$this->_actionPath = $path; // Who cares?
					$this->_actionFunction = $to['action'];
					if ( isset($to['arguments']) ) {
						$this->_actionArguments = (array)$to['arguments'];
					}
					return $application;
				}
				else {
					// Just another URI, so evaluate normally
					$path = ltrim($to, '/');
				}
			}
		}
		$uri = explode('/', ltrim($path, '/'), 2);
		$module = $uri[0] ?: $this->options->default_module;
//		$this->_module = $module;
		$actionPath = empty($uri[1]) ? '' : $uri[1];
		$application = $this->getControllerObject($module);
//var_dump($path, $actionPath); exit;
		$application->_fire('init');
		$_actions = $application->_getActionPaths(); // This and only this decides which Dispatch Type to use
		if ( is_array($_actions) ) {
			// Dispatch type "specific"
			// All _actions must start with a slash
			// The possibly present trailing slash has already been taken care of
			$actionPath = '/'.$actionPath;
//var_dump($actionPath);
			foreach ( $_actions AS $hookPath => $actionFunction ) {
				if ( $this->options->ignore_trailing_slash && '/' != $hookPath ) {
					$hookPath = rtrim($hookPath, '/');
				}
				$hookPath = strtr($hookPath, (array)$this->options->action_path_wildcard_aliases); // Aliases might be overkill?
				$hookPath = strtr($hookPath, (array)$this->options->action_path_wildcards); // Another strtr for every action hook... Too expensive?
				if ( 0 < preg_match('#^'.$hookPath.'$#', $actionPath, $matches) ) {
					if ( !$this->isCallableActionFunction($application, $actionFunction) ) {
						return $this->throwNotFound();
					}
					$this->_actionPath = $actionPath;
					$this->_actionFunction = $actionFunction;
					array_shift($matches);
					$this->_actionArguments = $matches;
					return $application;
				}
			}
			return $this->throwNotFound();
		}

		// Dispatch type "generic"
		$actionPath = rtrim($actionPath, '/');
		$actionDetails = explode('/', $actionPath);
//print_r($actionDetails);
		$actionFunction = array_shift($actionDetails) ?: $this->options->default_action;
		$afTranslation = $this->options->action_name_translation;
		if ( $afTranslation && is_callable($afTranslation) ) {
			$actionFunction = $afTranslation($actionFunction);
		}
		else {
			$actionFunction = str_replace('-', '_', $actionFunction);
		}
//var_dump($actionFunction);
		if ( !$this->isCallableActionFunction($application, $actionFunction) ) {
			return $this->throwNotFound();
		}
		$this->_actionPath = $actionPath;
		$this->_actionFunction = $actionFunction;
		$this->_actionArguments = $actionDetails;
		return $application;
	}

	protected function getControllerClassName( $module ) {
		if ( is_int(strpos($module, '\\')) ) {
			return $module;
		}
		/*if ( is_callable($this->options->module_to_class_translation) ) {
			$fn = $this->options->module_to_class_translation;
			$moduleClass = $fn($module);
		}
		else {*/
			$delim = $this->options->module_delim;
			$moduleParts = explode($delim, $module);
			if ( 1 < count($moduleParts) ) {
				$args = array();
				$mi = count($moduleParts);
				$li = 0;
				for ( $i=1; $i<$mi; $i++ ) {
					$submodule = $moduleParts[$i];
					if ( (string)(int)$submodule === $submodule ) {
						unset($moduleParts[$i]);
						$moduleParts[$li] .= '_N';
						$args[] = $submodule;
					}
					else {
						$li = $i;
					}
				}
				$moduleParts = array_values($moduleParts);
				$this->_moduleArguments = $args;
			}
//print_r($args);
//print_r($moduleParts);
			$n = count($moduleParts)-1;
			$moduleParts[$n] = $this->options->module_class_prefix . $moduleParts[$n] . $this->options->module_class_postfix;
			$moduleClass = implode('\\', $moduleParts);
//var_dump($moduleClass);
//echo "\n\n";
		/*}*/
		$namespacedModuleClass = 'app\\controllers\\'.$moduleClass;
		return $namespacedModuleClass;
	}

	protected function getControllerObject( $module ) {
		$namespacedModuleClass = $this->getControllerClassName($module);
		if ( !class_exists($namespacedModuleClass) ) { // Also _includes_ it and its dependancies/parents
			return $this->throwNotFound();
		}
		$application = new $namespacedModuleClass($this);
		return $application;
	}

	protected function isCallableActionFunction( \row\Controller $application, $actionFunction ) {
		return substr($actionFunction, 0, 1) != '_' && is_callable(array($application, $actionFunction));
	}

	protected function throwNotFound() {
		$exceptionClass = $this->options->not_found_exception;
		throw new $exceptionClass($this->requestPath);
	}


} // END Class Dispatcher


