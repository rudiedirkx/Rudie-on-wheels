<?php

namespace app\controllers;

use app\specs\Controller;

class dbController extends Controller {

	public function _pre_action() {
		echo '<pre>'."\n";
	}

	public function index() {
		return $this->columns();
	}

	public function columns( $table = 'users' ) {
		echo "PK columns:\n";
		$pkColumns = $this->db->_getPKColumns($table);
		print_r($pkColumns);
		echo "\nAll columns:\n";
		$columns = $this->db->_getTableColumns($table);
		print_r($columns);
	}

}


