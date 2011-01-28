<?php

namespace app\specs;

/**
 * No need to `use` classes SessionUser and View because
 * they exist in the same namespace: app\specs.
 */

class Controller extends \row\Controller {

	public function _init() {
		$this->user = new SessionUser;
		$this->tpl = new View($this);
		$this->tpl->viewsFolder = ROW_APP_PATH.'/views';
		$this->tpl->viewLayout = ROW_APP_PATH.'/views/_layout.php';
		$this->tpl->assign('app', $this);
		$this->db = $GLOBALS['db'];
	}

}


