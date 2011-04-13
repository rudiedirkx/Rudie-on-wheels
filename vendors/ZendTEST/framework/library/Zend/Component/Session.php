<?php

class Zend_Component_Session extends Zend_Component {

	function user() {
		static $user;
		if ( !$user ) {
			$user = new Zend_Component_Session_User;
		}
		return $user;
	}

	function acl() {
		static $acl;
		if ( !$acl ) {
			$acl = new Zend_Component_Session_ACL;
		}
		return $acl;
	}

}


