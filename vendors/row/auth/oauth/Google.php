<?php

namespace row\auth\oauth;

class Google {

	public function __construct( $consumer, $secret ) {
		$this->consumer = $consumer;
		$this->secret = $secret;
	}

	static public function array2get( $params ) {
		$get = array();
		foreach ( $params AS $k => $v ) {
			$get[] = urlencode($k).'='.urlencode($v);
		}
		return implode('&', $get);
	}

	public function nonce() {
		return md5(microtime().mt_rand());
	}

	public function getRequestToken() {
		$options = array(
			'oauth_consumer_key'		=> $this->consumer,
			'oauth_signature_method'	=> 'HMAC-SHA1',
			'oauth_signature'			=> $this->secret,
			'oauth_timestamp'			=> time(),
			'oauth_nonce'				=> $this::nonce(),
			'scope'						=> 'http://google.nl/calendar',
		);

header('Content-type: text/plain');
		$curl = curl_init('https://www.google.com/accounts/OAuthGetRequestToken?'.$this::array2get($options));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		if ( 1 or 'dev' ) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		}
		$response = curl_exec($curl);
var_dump(curl_error($curl));
		curl_close($curl);
var_dump($response);
exit;
	}

}


