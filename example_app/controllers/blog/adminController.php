<?php

namespace app\controllers\blog;

use app\controllers\blogController;

class adminController extends blogController {

	public function index() {
		echo 'Even though I am a subcontroller, I know nothing of my parent.';
	}

	public function view( $post ) {
		$post = $this->getPost($post);
		var_dump($post);
	}

}


