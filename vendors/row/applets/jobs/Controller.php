<?php

namespace row\applets\jobs;

class Controller extends \row\Controller {

	static protected $_actions = array(
		'/' => 'jobs',
		'/*/#/*/*' => 'job',
	);

	public function jobs() {
		echo 'index';
	}

	public function job( $loc = '', $id = 0 ) {
		echo '<pre>';
		print_r(func_get_args());
	}

}


