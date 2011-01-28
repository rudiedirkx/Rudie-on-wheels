<?php

namespace app\specs;

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


