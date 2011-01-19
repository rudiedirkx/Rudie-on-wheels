<?php

namespace row\database\adapter;

use row\database\adapter\PDO;

class PDOSQLite extends PDO {

	/* Reflection */
	public function _getTables() {
		$tables = $this->selectFieldsNumeric('sqlite_master', 'name', array('type' => 'table'));
print_r($tables); exit;
		$tables = array_map(function($r) {
			return reset($r);
		}, $tables);
		return $tables;
	}

	public function _getTableColumns( $table ) {
return array();
		$columns = $this->fetch('EXPLAIN '.$table);
		return $columns;
	}


	static public function initializable() {
		return in_array('pdo_sqlite', get_loaded_extensions());
	}


	public function connect() {
		$connection = $this->connectionArgs;
		$this->db = new \PDO('sqlite:'.$connection->path);
		$this->db->sqliteCreateFunction('IF', array('row\database\adapter\SQLite', 'fn_if'));
		$this->db->sqliteCreateFunction('RAND', array('row\database\adapter\SQLite', 'fn_rand'));
	}

	public function connected() {
		return is_object($this->query('SELECT 1 FROM sqlite_master'));
	}

}


