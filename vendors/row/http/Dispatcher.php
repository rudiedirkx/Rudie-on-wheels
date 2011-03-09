<?php

namespace row\http;

use row\core\Object;
use row\core\Options;

class Dispatcher extends Object {

	// The path to the Action (e.g. "/" or "/blog/categories" or "/blogs-12-admin/users/jim")
	public $requestPath = false;
	// The path up to the application (e.g.: "" or "/admin")
	public $requestBasePath = '';

	// The path that defines the module of this request (e.g. "blog" or "blogs-12-admin")
	public $_modulePath = '';
	// The Controller (class!) to be loaded (e.g. "app\controllers\blogController" or "app\controllers\blogs\adminController")
	public $_controller = '';
	// Arguments filtered from the $_modulePath (e.g. array(12))
	public $_moduleArguments = array();

	// The request path after the $_modulePath (e.g. "categories" or "users/jim")
	public $_actionPath = '';
	// The Action (method!) to be executed (e.g. "categories" or "users")
	public $_action = '';
	// Arguments to be passed to the Action method (e.g. array() or array("jim"))
	public $_actionArguments = array();

	// The optional Router object that contains pre-dispatch routes
	public $router; // typeof row\http\Router

	// The options object for this Dispatcher instance
	public $options; // typeof row\core\Options

	// This method is easily extended so that your personal preferences won't smudge my index.php
	static public function getDefaultOptions() {
		return Options::make(array(

			// In $requestPath "/blogs-12-admin/users/jim", the module delim is "-".
			// If you don't want to evaluate a multi level controller app, make this false or "".
			// The advantage of a multi level controller app: smaller controllers, more sensible Action names, $_moduleArguments
			'module_delim' => '-',

			// This module (not class!) will be used if none is specified in the URL (only for $requestPath "/")
			'default_module' => 'index',

			// If the URI doesn't evaluate to a Controller or existing Action method, the fallback Controller will be used
			// For example, you might want to make the URI "/flush-apc-cache" available, but not make 2 folders and a Controller for it.
			// All you have to do is make 1 fallback Controller (can be located anywhere) and create Action flush_apc_cache
			// You could also handle $requestPath "/" in the fallback Controller.
			'fallback_controller' => false,

			// The Action if none specified in the URI (e.g. $requestPath "/blog" equals "/blog/index")
			'default_action' => 'index',

			// This exception will be thrown if no valid fallback Controller Action is found and is caught in index.php
			'not_found_exception' => '\NotFoundException',

			// With these three, you can name your Controller classes anything like. You might like "mod_blog_controller" instead of "blogController".
			'module_class_prefix' => '',
			'module_class_postfix' => 'Controller',
			// This can be a callback. If a valid callback is found, it's executed **instead of** using the prefix & postfix.
			'module_to_class_translation' => false,

			// The following three options will do the same as the previous three but for the Action method name.
			'action_name_prefix' => '',
			'action_name_postfix' => 'Action',
			'action_name_translation' => false,

			// Wildcards to be used in the hooks of "specific" Controllers.
			// See row\applets\scaffolding\Controller for examples.
			// These wildcards are easily extended with expressions you often use (like VERSION for \d+\.\d\.\d) or USERNAME for [a-z][a-z0-9]{3,13})
			'action_path_wildcards' => Options::make(array(
				'INT'		=> '(\d+)',
				'STRING'	=> '([^/]+)',
				'DATE'		=> '(\d\d\d\d\-\d\d?\-\d\d?)',
			)),
			// Wildcard aliases so your hook paths are shorter and more readable
			'action_path_wildcard_aliases' => Options::make(array(
				'#' => 'INT',
				'*' => 'STRING',
			)),

			// Only for "specific" Controllers:
			// If true, $requestPath "/blog/categories" will do the same as "/blog/categories/"
			'ignore_trailing_slash' => true,

			// Only for Routes and "specific" Controllers:
			// It's very much reccomended that you keep this false!
			// If true, all paths (module & action) will be evaluated case-sensitive which will make a match less likely.
			'case_sensitive_paths' => false,
		));
	}


	public function __construct( $options = array() ) {
		$defaults = static::getDefaultOptions();
		$this->options = new Options($options, $defaults);
		$this->_fire('init');
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
		$this->_action = '';
		$actionPath = '/'.$actionPath;
		if ( !$this->options->case_sensitive_paths ) {
			$actionPath = strtolower($actionPath);
		}

		// is there a better way for this?
		$wildcards = (array)$this->options->action_path_wildcards;
		foreach ( (array)$this->options->action_path_wildcard_aliases AS $from => $to ) {
			if ( isset($wildcards[$to]) ) {
				$wildcards[$from] = $wildcards[$to];
			}
		}

		foreach ( $actions AS $hookPath => $actionFunction ) {
			if ( $this->options->ignore_trailing_slash && '/' != $hookPath ) {
				$hookPath = rtrim($hookPath, '/');
			}

//			$hookPath = strtr($hookPath, (array)$this->options->action_path_wildcard_aliases); // Aliases might be overkill?
//			$hookPath = strtr($hookPath, (array)$this->options->action_path_wildcards); // Another strtr for every action hook... Too expensive?
			$hookPath = strtr($hookPath, $wildcards);

			if ( !$this->options->case_sensitive_paths ) {
				$hookPath = strtolower($hookPath);
			}
			if ( 0 < preg_match('#^'.$hookPath.'$#', $actionPath, $matches) ) {
				array_shift($matches);
				$this->_actionArguments = $matches;
				$this->_action = $actionFunction;
				return true;
			}
		}
	}

	public function getController( $path, $routes = true ) {
		$path = ltrim($path, '/');

		// 1. Evaluate path into pieces
		$this->evaluatePath($path);

		$dontEvalActionHooks = false;
		if ( $routes && is_a($this->router, 'row\\http\\Router') ) {
			if ( $to = $this->router->resolve($path) ) {
				// 3. 
				if ( is_array($to) ) {
					foreach ( $to AS $k => $v ) {
						$this->{'_'.$k} = $v;
					}
					$dontEvalActionHooks = isset($to['action']);
				}
				else if ( is_string($to) ) {
					$this->evaluatePath(ltrim($to, '/'));
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
		if ( !($application = $this->getControllerObject($this->_controller)) ) {
			return $this->tryFallback();
		}

		if ( empty($dontEvalActionHooks) ) {
			// 6. 
			if ( is_array($_actions = $application->_getActionPaths()) ) {
				$this->evaluateActionHooks($_actions, $this->_actionPath);
			}
			else {
				$this->_action = $this->actionFunctionTranslation($this->_action);
			}
		}

		// 7. 
		if ( !$this->isCallableActionFunction($application, $this->_action) ) {
			return $this->tryFallback();
		}

		return $this->application = $application;
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
		return $this->options->action_name_prefix.str_replace('-', '_', $actionFunction).$this->options->action_name_postfix;
	}

	protected function getControllerClassName( $module ) {
		if ( is_int(strpos($module, '\\')) ) {
			return $module;
		}
		$delim = $this->options->module_delim;
		if ( $delim && 1 < ($mi=count($moduleParts = explode($delim, $module))) ) {
			$args = array();
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

	protected function getControllerObject( $module ) {
		$namespacedModuleClass = $this->getControllerClassName($module);
		if ( class_exists($namespacedModuleClass) ) {
			$application = new $namespacedModuleClass($this);
			$application->_fire('init');
			return $application;
		}
	}

	protected function isCallableActionFunction( \row\Controller $application, $actionFunction ) {
		return $actionFunction && substr($actionFunction, 0, 1) != '_' && is_callable(array($application, $actionFunction));
	}

	protected function throwNotFound() {
		$exceptionClass = $this->options->not_found_exception;
		throw new $exceptionClass($this->requestPath);
	}

	protected function tryFallback() {
		if ( $this->options->fallback_controller ) {
			// 5. 
			if ( $application = $this->getControllerObject($this->options->fallback_controller) ) {
				// reevaluate params for Action
				$this->evaluatePath('fallback'.$this->requestPath);

				// 6. 
				if ( is_array($_actions = $application->_getActionPaths()) ) {
					$this->evaluateActionHooks($_actions, $this->_actionPath);
				}
				else {
					$this->_action = $this->actionFunctionTranslation($this->_action);
				}

				// 7. 
				if ( $this->isCallableActionFunction($application, $this->_action) ) {
					return $application;
				}
			}
		}
		$this->throwNotFound();
	}

	public function caught( $exception ) {
		exit('Uncaught ['.get_class($exception).']: '.$exception->getMessage());
	}


	protected function _debug( $arr = null ) {
		is_array($arr) or $arr = (array)$this;
		foreach ( $arr AS $k => $v ) {
			if ( '_' != substr($k, 0, 1) ) {
				unset($arr[$k]);
			}
		}
		return $arr;
	}


} // END Class Dispatcher


