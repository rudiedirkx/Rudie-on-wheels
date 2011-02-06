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
 *	- initialize Views (Output)
 * What might come in handy: the database object. (The database
 * object is also available with `Model::dbObject()`.)
 */

class Controller extends \row\Controller {

	protected function _init() {
		parent::_init();

		// Make the session user always available in every controller:
		$this->user = new SessionUser;

		// Initialize Output/Views (used in 90% of controller actions):
		$this->tpl = new Output($this);
		$this->tpl->viewsFolder = ROW_APP_PATH.'/views';
		$this->tpl->viewLayout = ROW_APP_PATH.'/views/_layout.php';
		$this->tpl->assign('app', $this);

		// Might come in handy sometimes: direct access to the DBAL:
		$this->db = $GLOBALS['db'];
	}

}


