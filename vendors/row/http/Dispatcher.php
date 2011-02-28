<?php

namespace row\http;

use row\core\Object;
use row\core\Options;

class NotFoundException extends \RowException { } // Where to put this?

class Dispatcher extends Object {

	public $requestPath = false; // false means unset - will become a (might-be-empty) string
	public $requestBasePath = ''; // The path up to the application (e.g.: /admin/ or /row/)

	public $_modulePath = '';
	public $_controller = '';
	public $_moduleArguments = array();
	public $_actionPath = '';
	public $_action = '';
	public $_actionArguments = array();

//	public $cachedModules = array();
	public $router; // typeof row\http\Router

	public $options; // typeof row\core\Options

	static public function getDefaultOptions() { // Easily extendable without altering anything else
		return Options::make(array(
			'module_delim' => '-',
			'default_module' => 'index',
			'fallback_module' => false,
			'error_module' => false,
			'default_action' => 'index',
			'not_found_exception' => 'row\http\NotFoundException',
			'module_class_prefix' => '',
			'module_class_postfix' => 'Controller',
			'module_to_class_translation' => false,
			'action_name_translation' => false,
			'action_path_wildcards' => Options::make(array(
				'INT'		=> '(\d+)',
				'STRING'	=> '([^/]+)',
				'DATE'		=> '(\d\d\d\d\-\d\d?\-\d\d?)',
//				'ANYTHING'	=> '(.+)',
//				'VERSION'	=> '(\d+\.\d+\.\d+\)',
			)),
			'action_path_wildcard_aliases' => Options::make(array(
				'#' => 'INT',
				'*' => 'STRING',
			)),
			'ignore_trailing_slash' => true,
			'case_sensitive_paths' => false,
		));
	}

	public function __construct( $options = array() ) {
		$defaults = static::getDefaultOptions();
		$this->options = new Options($options, $defaults);
/*		$this->options->slashed_multi_module = '/' === $this->options->module_delim;
		if ( $this->options->slashed_multi_module ) {
			$this->cacheAllModules();
		}*/
		$this->_fire('init');
	}


/*	public function cacheAllModules() {
		if ( !$this->cachedModules ) {
			$controllerDir = ROW_APP_PATH.'/controllers/';
			$cdLength = strlen($controllerDir);
			$moduleName = create_function('$file', 'return substr($file, '.strlen($this->options->module_class_prefix).', '.(-1*(4+strlen($this->options->module_class_postfix))).');');
			$modules = array();
			$find = function($dir) use (&$find, $moduleName, &$modules, $cdLength) {
				$files = glob($dir);
				foreach ( $files AS $file ) {
					if ( is_dir($file) ) {
						$find($file.'/*');
					}
					else {
						$path = explode('/', substr($file, $cdLength));
						$pl = count($path)-1;
						$path[$pl] = $moduleName($path[$pl]);
						$modules[] = str_replace('_N', '/#', implode('/', $path));
					}
				}
			};
			$find($controllerDir.'*');
			$this->cachedModules = $modules;
		}
		return $this->cachedModules;
	}*/


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
		return $this->getController($f_path);
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
		$this->tryFallback();
	}

	public function getController( $path, $routes = true ) {
		$originalPath = $path;
		$path = ltrim($path, '/');

		// 1. Evaluate path into pieces
		$this->evaluatePath($path);

		$dontEvalActionHooks = false;
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
				$this->_action = $this->actionFunctionTranslation($this->_action);
			}
		}

		// 7. 
		if ( !$this->isCallableActionFunction($application, $this->_action) ) {
			return $this->tryFallback();
		}

		return $application;
	}

	protected function moduleClassTranslation( $moduleClass ) {
		// Custom translation
		if ( is_callable($translation = $this->options->module_to_class_translation) ) {
			return call_user_func($translation, $moduleClass);
		}
		// Default (simple) translation
		return $this->options->module_class_prefix . $moduleClass . $this->options->module_class_postfix;
	}

	protected function actionFunctionTranslation( $actionFunction ) {
		// Custom translation
		if ( is_callable($translation = $this->options->action_name_translation) ) {
			return call_user_func($translation, $actionFunction);
		}
		// Default (simple) translation
		return str_replace('-', '_', $actionFunction);
	}

	protected function getControllerClassName( $module ) {
		if ( is_int(strpos($module, '\\')) ) {
			return $module;
		}
		$delim = $this->options->module_delim;
		if ( $delim ) {
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
			$n = count($moduleParts)-1;
			$moduleParts[$n] = $this->moduleClassTranslation($moduleParts[$n]);
			$moduleClass = implode('\\', $moduleParts);
		}
		else {
			$moduleClass = $this->moduleClassTranslation($module);
		}
		$namespacedModuleClass = 'app\\controllers\\'.$moduleClass;
		return $namespacedModuleClass;
	}

	protected function getControllerObject( $module, $eval = true ) {
		$namespacedModuleClass = !$eval ? $module : $this->getControllerClassName($module);
		if ( !class_exists($namespacedModuleClass) ) { // Also _includes_ it
			return $this->tryFallback();
		}
		$this->application = new $namespacedModuleClass($this);
		return $this->application;
	}

	protected function isCallableActionFunction( \row\Controller $application, $actionFunction ) {
		return substr($actionFunction, 0, 1) != '_' && is_callable(array($application, $actionFunction));
	}

	protected function throwNotFound() {
		$exceptionClass = $this->options->not_found_exception;
		throw new $exceptionClass($this->requestPath);
	}

	protected function tryFallback() {
		static $firstTime = true;
		if ( $firstTime && $this->options->fallback_module ) {
			$firstTime = false;
			return $this->getApplication($this->options->fallback_module.$this->requestPath);
		}
		$this->throwNotFound();
	}

	public function caught( $exception ) {
		if ( $this->options->error_module ) {
			$uri = $this->options->error_module.'/index';
			$application = $this->getApplication($uri);
			$this->_actionArguments = array($exception);
			return $application->_run();

/*			$class = $this->getControllerClassName($this->options->error_module);
			if ( class_exists($class) ) {
				$this->evaluatePath('error/index');
				$this->_controller = $class;
				$application = $this->getControllerObject($this->_controller);
				if ( $this->isCallableActionFunction($application, $this->_action) ) {
					$this->_actionArguments = array($exception);
					$application->_fire('init');
					return $application->_run();
				}
			}*/
		}
		exit('Uncaught exception: '.$exception->getMessage());
	}


} // END Class Dispatcher


