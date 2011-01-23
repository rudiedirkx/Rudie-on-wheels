<?php

namespace app\controllers;

use row\utils;

class dbsecretsController extends utils\scaffolding\Controller {

	static protected $config = array(
		'allowed_ip_addresses' => array('127.0.0.1'),
	);

	protected function _init() {
		if ( !isset($_GET['pass']) || $_GET['pass'] !== 'oele' ) {
//		if ( !in_array($_SERVER['REMOTE_ADDR'], $this->config('allowed_ip_addresses')) ) {
			throw new \row\http\NotFoundException($this->_uri.' ( the password is "<a href="'.$this->_uri.'?pass=oele">oele</a>" you poepchinees =p )');
		}
		parent::_init();
	}

	public function _url( $action = '', $more = '' ) {
		$url = parent::_url($action, $more);
		$url .= '?pass=' . ( isset($_GET['pass']) ? $_GET['pass'] : '' );
		return $url;
	}

}


