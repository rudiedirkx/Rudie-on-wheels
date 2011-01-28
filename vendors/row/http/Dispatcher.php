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

//	public $_module = ''; // deprecated
	public $_modulePath = '';
	public $_controller = '';
	public $_moduleArguments = array();
	public $_actionPath = '';
	public $_action = '';
//	public $_actionFunction = ''; // deprecated
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
			'action_name_translation' => false,
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


	public function evaluatePath($path) {
		$p = explode('/', $path);
		$this->_modulePath = array_shift($p) ?: $this->options->default_module;
		$this->_controller = '';
		$this->_moduleArguments = array();
		$this->_actionPath = implode('/', $p);
		$this->_action = array_shift($p) ?: $this->options->default_action;
		$this->_actionArguments = $p;
	}

	public function evaluateActionHooks( $actions, $actionPath ) {
		$actionPath = '/'.$actionPath;
		foreach ( $actions AS $hookPath => $actionFunction ) {
			if ( $this->options->ignore_trailing_slash && '/' != $hookPath ) {
				$hookPath = rtrim($hookPath, '/');
			}
			$hookPath = strtr($hookPath, (array)$this->options->action_path_wildcard_aliases); // Aliases might be overkill?
			$hookPath = strtr($hookPath, (array)$this->options->action_path_wildcards); // Another strtr for every action hook... Too expensive?
			if ( 0 < preg_match('#^'.$hookPath.'$#', $actionPath, $matches) ) {
				array_shift($matches);
				$this->_actionArguments = $matches;
				$this->_action = $actionFunction;
				return true;
			}
		}
		$this->throwNotFound();
	}

	public function getController( $path, $routes = true ) {
		$originalPath = $path;
		$path = ltrim($path, '/');

//		$p = explode('/', $path);
//		$this->_module = $p[0];

		// 1. Evaluate path into pieces
		$this->evaluatePath($path);
		// 2. We now have everything we need, except for the Controller class
		// 3. Run Router and overwrite pieces OR reevaluate new path
		// 4. If the Router didn't set the _controller, evaluate the _modulePath into a valid class
		// 5. Get the Controller object OR throw 404
		// 6. If the Router didn't set an actionFunction and this controller is of type "specific", run its _actions
		// 7. We now know if this path can be dispatched... Run or throw 404
//echo '<pre>';

		if ( $routes && $this->router ) {
//			$path != '' || $path = '/';
			if ( $to = $this->router->resolve($path) ) {

				// 3. 
				if ( is_array($to) ) {
					foreach ( $to AS $k => $v ) {
						$this->{'_'.$k} = $v;
					}
					$dontEvalActionHooks = isset($to['action']);
				}
				else if ( is_string($to) ) {
					$this->evaluatePath($to);
				}

				/* if ( is_array($to) && isset($to['controller'], $to['action']) ) { // Don't evaluate URI like 'normal'
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
				} */

			}
		}

		// 4. 
		if ( !$this->_controller ) {
			$this->_controller = $this->getControllerClassName($this->_modulePath);
		}
		else if ( !is_int(strpos($this->_controller, '\\')) ) {
			$this->_controller = $this->getControllerClassName($this->_modulePath);
		}

		// 5. 
		$application = $this->getControllerObject($this->_controller);
		$application->_fire('init');

		// 6. 
		if ( empty($dontEvalActionHooks) ) {
			if ( is_array($_actions = $application->_getActionPaths()) ) {
				$this->evaluateActionHooks($_actions, $this->_actionPath); // Might throw 404
			}
			else {
				if ( is_callable($translation = $this->options->action_name_translation) ) {
					$this->_action = call_user_func($translation, $this->_action);
				}
				else {
					$this->_action = str_replace('-', '_', $this->_action);
				}
			}
		}

		// 7. 
		if ( !$this->isCallableActionFunction($application, $this->_action) ) {
			return $this->throwNotFound();
		}

//print_r($application);
//exit;

		return $application;
	}

	protected function getControllerClassName( $module ) {
		if ( is_int(strpos($module, '\\')) ) {
			return $module;
		}
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
		if ( is_callable($translation = $this->options->module_to_class_translation) ) {
			$moduleParts[$n] = call_user_func($translation, $moduleParts[$n]);
		}
		else {
			$moduleParts[$n] = $this->options->module_class_prefix . $moduleParts[$n] . $this->options->module_class_postfix;
		}
		$moduleClass = implode('\\', $moduleParts);
//var_dump($moduleClass);
//echo "\n\n";
		$namespacedModuleClass = 'app\\controllers\\'.$moduleClass;
		return $namespacedModuleClass;
	}

	protected function getControllerObject( $module, $eval = true ) {
		$namespacedModuleClass = !$eval ? $module : $this->getControllerClassName($module);
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


