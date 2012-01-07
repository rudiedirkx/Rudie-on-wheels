<?php

namespace row\http;

use row\core\Object;
use row\core\Vendors;
use row\core\Options;
use row\core\APC;
use row\core\RowException;
use row\Output;
use row\Controller AS ROWController;

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

	// Action info
	public $actionInfo;

	// Params passed through _internal
	public $params; // typeof Options

	// The optional Router object that contains pre-dispatch routes
	public $router; // typeof row\http\Router

	// The mandatory controllers map
	public $controllers; // typeof Array

	// The options object for this Dispatcher instance
	public $options; // typeof row\core\Options

	// This method is easily extended so that your personal preferences won't smudge my index.php
	public function getOptions() {
		return Options::make(array(

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

			// Wildcards to be used in Routes, Controller mapping and Action mapping.
			// See row\applets\scaffolding\Controller for examples.
			// Extend these with expressions you often use (like VERSION for \d+\.\d\.\d) or USERNAME for [a-z][a-z0-9]{3,13})
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

		$this->_fire('post_dispatch');

		return $controller;
	}


	public function evalActionHooks( $actions, $actionPath ) {
		$actionPath = '/'.$actionPath;
		if ( !$this->options->case_sensitive_paths ) {
			$actionPath = strtolower($actionPath);
		}

		foreach ( $actions AS $hookPath => $actionFunction ) {
			if ( '/' != $hookPath ) {
				$hookPath = rtrim($hookPath, '/');
			}

			$regex = $this->routeToRegex($hookPath);
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


	protected function getActionInfo( ROWController $controller, $actionPath ) {
		if ( is_array($actions = $controller->_getActionPaths()) ) {
			return $this->evalActionHooks($actions, $actionPath);
		}

		return $this->evalActionPath($actionPath);
	}


	public function routeToRegex( $uri ) {
		$wildcards = $this->options->action_path_wildcards;

		$regex = strtr($uri, $wildcards);

		return $regex;
	}


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

		if ( is_array($to) ) {
			if ( isset($to['controller'], $to['action']) ) {
				$controller = $this->getControllerObject($to['controller']);

				if ( $controller ) {
					// 3. Find action match
					$actionInfo = array(
						'action' => $to['action'],
						'arguments' => isset($to['arguments']) ? $to['arguments'] : array(),
					);
					if ( $this->isCallableActionFunction($controller, $actionInfo) ) {
						return $this->prepController($controller, $actionInfo);
					}
				}
			}
		}

		// 2. Find controller match
		$controllers = $this->getControllersMap();
		$fallback = $controllers[''];
		foreach ( $controllers AS $route => $module ) {
			$regex = $this->routeToRegex($route);
			$regex = '#^('.$regex.'(?:/|$))#';

			if ( preg_match($regex, $uri, $match) ) {
				$actionPath = ltrim((string)substr($uri, strlen($match[1])), '/');

				// 3. Find action match
				$controller = $this->getControllerObject($module);
				$actionInfo = $this->getActionInfo($controller, $actionPath);

				if ( $this->isCallableActionFunction($controller, $actionInfo) ) {
					return $this->prepController($controller, $actionInfo);
				}
			}
		}

		// Mandatory fallback

		// 3. Find action match
		$actionPath = $uri;
		$controller = $this->getControllerObject($fallback);
		$actionInfo = $this->getActionInfo($controller, $actionPath);
		if ( $this->isCallableActionFunction($controller, $actionInfo) ) {
			return $this->prepController($controller, $actionInfo);
		}

		// 404 Not found
		$this->throwNotFound();
	}


	protected function prepController( ROWController $controller, Array $actionInfo ) {
		$this->actionInfo = $actionInfo;
		return $controller;
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


	protected function getControllerObject( $class ) {
		if ( !is_int(strpos($class, '\\')) ) {
			$class = $this->moduleClassTranslation($class);
		}

		if ( Vendors::class_exists($class) ) {
			$controller = new $class($this);

			return $controller;
		}
	}


	protected function isCallableActionFunction( ROWController $application, $actionInfo ) {
		if ( $actionInfo ) {
			$action = $actionInfo['action'];
			$arguments = $actionInfo['arguments'];

			$actions = $application->_getActionFunctions();
			if ( in_array(strtolower($action), $actions) ) {
				$refl = new \ReflectionClass($application);
				$method = $refl->getMethod($action);
				$required = $method->getNumberOfRequiredParameters();
				return $required <= count($arguments);
			}
		}
	}


	public function throwNotFound() {
		$exceptionClass = $this->options->not_found_exception;
		throw new $exceptionClass('/'.$this->requestPath);
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


