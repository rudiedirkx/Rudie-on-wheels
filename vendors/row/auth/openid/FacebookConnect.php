<?php

namespace row\auth\openid;

use row\auth\OpenID AS OpenIDParent;
use row\Output;

class FacebookConnect {

	public $appId;
	public $appSecret;
	public $redirectURI;

	public function __construct( $appId, $appSecret, $options = array() ) {
		$this->appId = $appId;
		$this->appSecret = $appSecret;

		$this->options = options($options);

		$this->redirectURI = $this->options->redirect_uri;
	}

	public function userInfo( $params ) {
		if ( is_string($params) ) {
			// params = access token
			$params = array(
				'access_token' => $params,
			);
		}

		// facebook url
		$url = $this->requestURL(__FUNCTION__, $params);

		// request
		$user = @file_get_contents($url);

		// response
		if ( $user ) {
			// format: json
			$user = json_decode($user);

			// valid json
			if ( $user ) {
				// stdClass object
				return $user;
			}
		}
	}

	public function validate( $code = null ) {
		// code from global request?
		if ( !$code ) {
			// no code!?
			if ( empty($_GET['code']) ) {
				return;
			}

			// default location from facebook redirect
			$code = $_GET['code'];
		}

		// facebook url
		$url = $this->requestURL(__FUNCTION__, array(
			'code' => $code,
		));

		// request
		$auth = @file_get_contents($url);

		// response
		if ( $auth ) {
			// format: query string
			parse_str($auth, $response);

			// param: access_token
			if ( $response && isset($response['access_token']) ) {
				// get user info
				return $this->userInfo($response);
			}
		}
	}

	public function login( $redirect = false ) {
		$url = $this->requestURL(__FUNCTION__);

		if ( $redirect ) {
			return redirect($url);
		}

		return $url;
	}

	public function requestURL( $type, $params = array() ) {
		$O = Output::$class;

		switch ( $type ) {
			// login -- no params
			case 'login':
				$params['client_id'] = $this->appId;
				$params['redirect_uri'] = $O::url($this->redirectURI, array('absolute' => true));
				break;

			// validate -- params: `code`
			case 'validate':
				$params['client_id'] = $this->appId;
				$params['client_secret'] = $this->appSecret;
				$params['redirect_uri'] = $O::url($this->redirectURI, array('absolute' => true));
				break;

			// userInfo -- params: `access_token`
			case 'userInfo':
				break;

			default:
				return '';
		}

		$urls = array(
			'login' => 'https://www.facebook.com/dialog/oauth',
			'validate' => 'https://graph.facebook.com/oauth/access_token',
			'userInfo' => 'https://graph.facebook.com/me',
		);

		return $urls[$type] . '?' . http_build_query($params);
	}

}