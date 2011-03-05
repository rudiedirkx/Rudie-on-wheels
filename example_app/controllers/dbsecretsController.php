<?php

namespace app\controllers;

/**
 * This Controller isn't actuall necessary. We can reference/link
 * to applet Controllers with a Route. If you use a Route, only the
 * base applet Controller is used and you can't add anything specific
 * (like a password or IP check)
 * 
 * A Route exists for the Scaffolding applet: /scaffolding/*
 * 
 * But like this, we can add some specific, unncessary stuff. Note how
 * the _url method is extended to facilitate the password check throughout
 * the applet automatically.
 */

class dbsecretsController extends \row\applets\scaffolding\Controller {

	static protected $config = array(
		'allowed_ip_addresses' => array('127.0.0.1'),
	);

	// Security option A: Check for password
	// Security option B: Check for allowed IP
	// Obviously you shouldn't add the password to the NotFoundException
	// (and you should hash it).
	protected function _init() {
		if ( !isset($_GET['pass']) || $_GET['pass'] !== 'oele' ) {
//		if ( !in_array($_SERVER['REMOTE_ADDR'], $this->config('allowed_ip_addresses')) ) {
			$pwdMessage = ' ( the password is "<a href="'.$this->_uri.'?pass=oele">oele</a>" you poepchinees =p )';
//			throw new \NotFoundException($this->_uri.$pwdMessage);
			exit('Access denied!'.$pwdMessage);
		}
		parent::_init();
	}

	public function _url( $action = '', $more = '' ) {
		$url = parent::_url($action, $more);
		$url .= '?pass=' . ( isset($_GET['pass']) ? $_GET['pass'] : '' );
		return $url;
	}

}


