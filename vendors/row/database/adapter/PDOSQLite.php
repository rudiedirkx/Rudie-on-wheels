<?php

namespace row\database\adapter;

use row\database\adapter\PDO;

class PDOSQLite extends PDO {

	public function connect() {
		$connection = $this->connectionArgs;
		$this->db = new \PDO('sqlite:'.$connection->path);
	}

}


