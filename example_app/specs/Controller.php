<?php

namespace app\specs;

/**
 * No need to `use` classes SessionUser and Output because
 * they exist in the same namespace: app\specs.
 * 
 * Your should extend row\Controller because it contains
 * no functionality besides the very basic Controller.
 * Below are two (also very basic) examples of what you
 * could/should do in the init:
 *	- initialize SessionUser
 *	- initialize Controller (Action) ACL
 *	- initialize Views (Output)
 * What might come in handy: the database object. (The database
 * object is also available with `Model::dbObject()`.)
 */

abstract class Controller extends \row\Controller {

	protected function _init() {
		// DON'T do `parent::_init();` because I don't want to load the standard ROW crap ;)

		// Might come in handy sometimes: direct access to the DBAL:
		$this->db = $GLOBALS['db'];

		// Make the session user always available in every controller:
		$this->user = SessionUser::user(); // $this->getComponent('app\specs\SessionUser');

		// And the ACL
		$this->acl = $this->getComponent('app\\specs\\ControllerACL');

		// Initialize Output/Views (used in 90% of controller actions):
		$this->tpl = new Output($this);
		$this->tpl->viewsFolder = ROW_APP_PATH.'/views';
		$this->tpl->viewLayout = '_blogLayout';
		$this->tpl->assign('app', $this);

		$this->_constructComponents();
	}

}


