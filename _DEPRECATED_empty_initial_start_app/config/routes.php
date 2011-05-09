<?php

use row\http\Router;

$router = new Router;


/**
 * Make as few routes as possible! It's much faster to make
 * internal routes with a "specific" Controller.
 * The more routes you add here, the work will be done EVERY
 * request no matter the Controller.
 * 
 * Advised routes are:
	- Crazy URLs (e.g. with reversed arguments)
	- Home: / (the only URL with no Controller)
 */

$router->add('/$', array('controller' => 'app\controllers\helloController'));
// is the same as
// $router->add('/$', array('modulePath' => 'hello'));
// is the same as
// $router->add('/$', 'hello');
// is the same as
// $router->add('/$', 'hello/index');


