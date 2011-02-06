<?php

namespace row\auth;

use row\core\Object;

class ControllerACL extends Object {

	public function __tostring() {
		return 'ACL';
	}

	protected $application;

	protected $acl = array();

	public function __construct( \row\Controller $application ) {
		$this->application = $application;
	}

	public function add( $zones, $actions ) {
		foreach ( (array)$zones AS $zone ) {
			foreach ( (array)$actions AS $action ) {
				$this->acl[$action][$zone] = true;
			}
		}
	}

	public function remove( $zones, $actions ) {
		
	}

	public function check( $action ) {
		if ( isset($this->acl[$action]) ) {
			foreach ( $this->acl[$action] AS $zone => $x ) {
				if ( !$this->checkAccess($zone) ) {
					return $this->accessFail( $zone, $action );
				}
			}
		}
		return true;
	}

	/**
	 * Obviously you're gonne want to extend this to something like this:
		return $this->application->user->hasAccess($zone);
	 * Or something completely different... Whatever you like. But do
	 * it in your extension (e.g. app\specs\ControllerACL).
	 */
	public function checkAccess( $zone ) {
		return true;
	}

	protected function accessFail( $zone, $action ) {
		return false;
	}

}


