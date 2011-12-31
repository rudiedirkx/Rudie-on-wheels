<?php

namespace row\http;

use row\core\Object;
use row\core\Vendors;
use row\core\Options;
use row\core\APC;
use row\core\RowException;
use row\Output;

class NotFoundException extends RowException {}

abstract class Dispatcher extends Object {

	public $_id = 0; // debug

	// The path to the Action (e.g. "" or "blog/categories" or "blogs-12-admin/users/jim")
	public $requestPath = false;
	// The path up to the application URI's (e.g.: "/" or "/admin/" or "/admin/index.php/")
	public $requestBasePath = '';
	// The path up to the application files (e.g.: "/" or "/admin/")
	public $fileBasePath = '';
	// The filename of the entryscript (all URI's are routed there, preferably with a URL rewrite) (e.g.: "index.php" or "backend_dev.php")
	public $entryScript = '';

	// The path that defines the module of this request (e.g. "blog" or "blogs-12-admin")
#	public $_modulePath = '';
	// The Controller (class!) to be loaded (e.g. "app\controllers\blogController" or "app\controllers\blogs\adminController")
#	public $_controller = '';
	// Arguments filtered from the $_modulePath (e.g. array(12))
#	public $_moduleArguments = array();

	// The request path after the $_modulePath (e.g. "categories" or "users/jim")
#	public $_actionPath = '';
	// The Action (method!) to be executed (e.g. "categories" or "users")
#	public $_action = '';
	// Arguments to be passed to the Action method (e.g. array() or array("jim"))
#	public $_actionArguments = array();

	// Action info
	public $actionInfo;

	// Params passed through _internal
	public $params; // typeof Options

	// Whether this dispatch comes from cache
	//protected $fromCache = false;

	// The optional Router object that contains pre-dispatch routes
	public $router; // typeof row\http\Router

	// The mandatory controllers map
	public $controllers; // typeof Array

	// The options object for this Dispatcher instance
	public $options; // typeof row\core\Options

	// This method is easily extended so that your personal preferences won't smudge my index.php
	public function getOptions() {
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
			'not_found_exception' => 'row\http\NotFoundException',

			// With these three, you can name your Controller classes anything like. You might like "mod_blog_controller" instead of "blogController".
			'module_class_prefix' => '',
			'module_class_postfix' => 'Controller',

			// The following three options will do the same as the previous three but for the Action method name.
			'action_name_prefix' => '',
			'action_name_postfix' => 'Action',

			// Wildcards to be used in the hooks of "specific" Controllers.
			// See row\applets\scaffolding\Controller for examples.
			// These wildcards are easily extended with expressions you often use (like VERSION for \d+\.\d\.\d) or USERNAME for [a-z][a-z0-9]{3,13})
			'action_path_wildcards' => array(
				'#'			=> '(\d+)',
				'%'			=> '([^/]+)',
				'*'			=> '(.+)',
				'DATE'		=> '(\d{4}-\d\d?\-\d\d?)',
			),

			// It's very much reccomended that you keep this FALSE!
			// If true, all paths (module & action) will be evaluated case-sensitive, which will make a match less likely.
			'case_sensitive_paths' => false,
		));
	}


	public function __construct( $controllers ) {
		$this->controllers = $controllers;
		$this->options = $this->getOptions();

		$this->_id = rand(1000, 9999);

		//$this->cacheLoad();

		$this->getRequestPath();

		$GLOBALS['Dispatcher'] = $this;

		$this->_fire('init');
	}

	protected function _init() {
		
	}

	protected function _post_dispatch() {
		//$this->cacheCurrentDispatch();
	}


	public function getRequestPath() {
		$dirname = function( $path ) {
			return rtrim(str_replace('\\', '/', dirname($path)), '/');
		};

		// entry script: index.php
		$this->entryScript = basename($_SERVER['SCRIPT_NAME']);

		// request path / uri: admin/users/view/14 & uri base path
		if ( 0 === strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']) ) {
			$requestBase = $_SERVER['SCRIPT_NAME'] . '/';
			$uri = ltrim(substr($_SERVER['REQUEST_URI'], strlen($requestBase)-1), '/');
		}
		else {
			$requestBase = $dirname($_SERVER['SCRIPT_NAME']) . '/';
			$uri = ltrim(substr($_SERVER['REQUEST_URI'], strlen($requestBase)-1), '/');
		}
		is_int($p = strpos($uri, '?')) && $uri = substr($uri, 0, $p);
		$uri = rtrim($uri, '/');

		$this->requestBasePath = $requestBase;
		$this->requestPath = $uri;

		// file base path: /
		$fileBase = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
		$fileBase == '/' or $fileBase .= '/';

		$this->fileBasePath = $fileBase;

	}


	public function setRouter( \row\http\Router $router ) {
		$router->setDispatcher($this);
		$this->router = $router;
	}


	public function getApplication( $f_path = null ) {
		$path = $f_path ? $f_path : $this->requestPath;

		$controller = $this->getController($f_path);
		$this->application = $controller;

		$GLOBALS['Application'] = $this;

		//if ( !$this->fromCache ) {
			$this->_fire('post_dispatch');
		//}

		return $controller;
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

		foreach ( $actions AS $hookPath => $actionFunction ) {
			if ( '/' != $hookPath ) {
				$hookPath = rtrim($hookPath, '/');
			}

			$regex = $this->uriToRegex($hookPath);
			$regex = '#^'.$regex.'$#';

			if ( $this->options->case_sensitive_paths ) {
				$regex .= 'i';
			}

			if ( 0 < preg_match($regex, $actionPath, $match) ) {
				$args = array_slice($match, 1);
				return array(
					'action' => $actionFunction,
					'arguments' => $args,
				);
			}
		}
	}

	protected function evalActionPath( $actionPath ) {
		$parts = explode('/', $actionPath);

		$action = array_shift($parts) ?: $this->options->default_action;
		$action = $this->actionFunctionTranslation($action);

		return array(
			'action' => $action,
			'arguments' => $parts,
		);
	}

	protected function uriToRegex( $uri ) {
		$wildcards = $this->options->action_path_wildcards;

		$regex = strtr($uri, $wildcards);

		return $regex;
	}


	/* experimental *
	public $cache = array();
	public $cacheChanged = false;

	public function cacheLoad() {
		if ( array() === $this->cache ) {
			$this->cache = APC::get('dispatches', array());
			$self = $this;
			register_shutdown_function(function() use ($self) {
				if ( $self->cacheChanged ) {
					APC::put('dispatches', $self->cache);
				}
			});
		}
	}

	public function cachePut( $path, $target ) {
		if ( false !== $this->cache ) {
			$this->cache[$path] = $target;
			$this->cacheChanged = true;
		}
	}

	public function cacheGet( $path ) {
		if ( false !== $this->cache ) {
			if ( isset($this->cache[$path]) ) {
				return $this->cache[$path];
			}
		}
	}

	public function cacheClear() {
		$this->cache = array();
		$this->cacheChanged = false;
		return APC::clear('dispatches');
	}

	protected function cacheCurrentDispatch() {
		$dispatch = array(
			'_modulePath' => $this->_modulePath,
			'_controller' => get_class($this->application),
			'_moduleArguments' => $this->_moduleArguments,
			'_actionPath' => $this->_actionPath,
			'_action' => $this->_action,
			'_actionArguments' => $this->_actionArguments,
		);
		$this->cachePut($this->requestPath, $dispatch);
	}
	/* experimental */


	public function getController( $uri, $routes = true ) {

		// 1. Find Router match
		// 2. Find controller match
		// 3. Find action match

		// 1. Find Router match
		if ( $routes && is_a($this->router, 'row\\http\\Router') ) {
			if ( $to = $this->router->resolve($uri) ) {
				if ( is_string($to) ) {
					$uri = $to;
				}
			}
		}

		// 2. Find controller match
		$controllers = $this->getControllersMap();
		foreach ( $controllers AS $curi => $class ) {
			$regex = $this->uriToRegex($curi);
			$regex = '#^('.$regex.'(?:/|$))#';

			if ( preg_match($regex, $uri, $match) ) {
				// 3. Find action match
				$actionPath = ltrim((string)substr($uri, strlen($match[1])), '/');

				$controller = $this->getControllerObject($class);
				if ( is_array($actions = $controller->_getActionPaths()) ) {
					$actionInfo = $this->evaluateActionHooks($actions, $actionPath);
				}
				else {
					$actionInfo = $this->evalActionPath($actionPath);
				}

				if ( $actionInfo ) {
					if ( $this->isCallableActionFunction($controller, $actionInfo['action'], $actionInfo['arguments']) ) {
						$controller->_fire('init');
						$this->actionInfo = $actionInfo;
						return $controller;
					}
				}
			}
		}







		/*$this->params = options($this->params);

		/* experimental *
		if ( null !== ($target = $this->cacheGet($path)) ) {
			foreach ( $target AS $k => $v ) {
				$this->$k = $v;
			}
			if ( $application = $this->getControllerObject($this->_controller) ) {
				$this->fromCache = true;
				return $application;
			}
		}
		/* experimental *


		$path = ltrim($path, '/');

		// 1. Evaluate path into pieces
		$this->evaluatePath($path);
//print_r($this->_debug());

		$dontEvalActionHooks = false;
		if ( $routes && is_a($this->router, 'row\\http\\Router') ) {
			if ( $to = $this->router->resolve($path) ) {
				// 3. 
				if ( is_array($to) ) {
//print_r($to);
					foreach ( $to AS $k => $v ) {
						$this->{'_'.$k} = $v;
					}
//print_r($this);
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
			$this->_controller = $this->getControllerClassName($this->_controller);
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

		return $application;*/
	}

	protected function getControllersMap() {
		$controllers = array();

		foreach ( $this->controllers AS $uri => $class ) {
			if ( is_int($uri) ) {
				$uri = $class = (string)substr($class, 1);
			}
			else {
				$uri = (string)substr($uri, 1);
			}

			

			$controllers[$uri] = $class;
		}

		return array_reverse($controllers);
	}

	protected function moduleClassTranslation( $moduleClass ) {
		// Default (simple) translation
		return 'app\\controllers\\' . $this->options->module_class_prefix . str_replace('/', '\\', $moduleClass) . $this->options->module_class_postfix;
	}

	protected function actionFunctionTranslation( $actionFunction ) {
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

	protected function getControllerObject( $class ) {
		if ( !is_int(strpos($class, '\\')) ) {
			$class = $this->moduleClassTranslation($class);
		}

		$controller = new $class($this);

		return $controller;
	}

	protected function isCallableActionFunction( \row\Controller $application, $action, $arguments ) {
		$actions = $application->_getActionFunctions();
		if ( in_array(strtolower($action), $actions) ) {
			$refl = new \ReflectionClass($application);
			$method = $refl->getMethod($action);
			$required = $method->getNumberOfRequiredParameters();
			return $required <= count($arguments);
		}
	}

	public function throwNotFound() {
		$exceptionClass = $this->options->not_found_exception;
		throw new $exceptionClass('/'.$this->requestPath);
	}

	protected function tryFallback() {
		if ( $this->options->fallback_controller ) {
			// 5. 
			if ( $application = $this->getControllerObject($this->options->fallback_controller) ) {
				// reevaluate params for Action
				$this->evaluatePath('fallback/'.$this->requestPath);

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
//		ob_end_clean();

		exit('Uncaught ['.get_class($exception).']: '.$exception->getMessage());
	}


	public function _redirect( $location, $exit = true ) {
		$goto = 0 === strpos($location, '/') || in_array(substr($location, 0, 6), array('http:/', 'https:')) ? $location : Output::url($location);
		header('Location: '.$goto);
		if ( $exit ) {
			exit;
		}
	}

	public function _internal( $location, $params = array() ) {
		if ( is_string($location) ) {
			$dispatcher = new static(array());
			$dispatcher->options = $this->options;
			$dispatcher->router = $this->router;
			$dispatcher->params = $params;

			$application = $dispatcher->getApplication($location);
			return $application->_run();
		}
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


