<?php

namespace app\controllers;

use row\utils;

class dbsecretsController extends utils\sandboxController {

	static protected $config = array(
		'allowed_ip_addresses' => array('127.0.0.1'),
	);

	protected function _init() {
		if ( !in_array($_SERVER['REMOTE_ADDR'], $this->config('allowed_ip_addresses')) ) {
			throw new \row\http\NotFoundException($this->_dispatcher->requestPath);
		}
	}

}


