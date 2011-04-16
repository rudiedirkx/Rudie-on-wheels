<?php

namespace row;

abstract class YiiController extends Controller {

	public function _run() {
		$this->_fire('pre_action');

		// Put action arguments in $_GET
		$args = $this->_dispatcher->_actionArguments;
		for ( $i=0; $i < count($args); $i+=2 ) {
			$_GET[$args[$i]] = isset($args[$i+1]) ? $args[$i+1] : '';
		}

		// Map $_GET to actual action method arguments
		$method = new \ReflectionMethod($this, $this->_dispatcher->_action);
print_r($method);
		if ( 0 == $method->getNumberOfParameters() ) {
			$action = $this->_dispatcher->_action;
			$r = $this->$action();
		}
		else {
			foreach ( $method->getParameters() as $i => $param ) {
				$name = $param->getName();
				if ( isset($_GET[$name]) ) {
					if ( $param->isArray() ) {
						$params[] = is_array($_GET[$name]) ? $_GET[$name] : array($_GET[$name]);
					}
					else if ( !is_array($_GET[$name]) ) {
						$params[] = $_GET[$name];
					}
					else {
						return $this->_dispatcher->throwNotFound();
					}
				}
				else if ( $param->isDefaultValueAvailable() ) {
					$params[] = $param->getDefaultValue();
				}
				else {
					return $this->_dispatcher->throwNotFound();
				}
			}
			$r = $method->invokeArgs($this, $params);
		}

		$this->_fire('post_action');
		return $r;
	}

}


