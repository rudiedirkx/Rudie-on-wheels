<?php

namespace app\controllers;

use app\specs\Controller;
use row\auth\oauth\Google;

class authController extends Controller {

	public function token() {
		echo '<pre>';
		var_dump($_GET['token']);
	}

	public function start() {
		$google = new Google('hotblocks.nl', 'GtbwV0sLo/KugjrbWg6qhWoE');
		$google->getRequestToken();
	}

}


