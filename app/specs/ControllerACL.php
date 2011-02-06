<?php

namespace app\specs;

/**
 * 
 */

class ControllerACL extends \row\auth\ControllerACL {

	public function checkAccess( $zone ) {
		return $this->application->user->hasAccess($zone);
	}

	protected function accessFail( $zone, $action ) {
		exit('You no have the access ('.$zone.')!');
	}

}


