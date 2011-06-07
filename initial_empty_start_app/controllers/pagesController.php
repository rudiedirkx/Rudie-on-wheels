<?php

namespace app\controllers;

use app\specs\Controller;

class pagesController extends Controller {

	public function page( $page = 'Home' ) {
		return $this->tpl->display(get_defined_vars()); // automatically get view: pages/page
	}

}


