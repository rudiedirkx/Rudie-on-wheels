<?php

namespace app\specs;

abstract class Controller extends \row\Controller {

	protected function _init() {
		parent::_init();

		$this->db = $GLOBALS['db'];

		$this->tpl = new Output($this);
		$this->tpl->viewLayout = '_layout';
		$this->tpl->assign('application', $this);

	}

}


