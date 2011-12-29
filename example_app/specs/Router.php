<?php

namespace app\specs;

class Router extends \row\http\Router {

	public function routeToRegex( $from ) {
//		$from = preg_replace('/%([a-z_]+)/', '(?P<\\1>[a-z0-9_]+)', $from);

		return $from;
	}

}


