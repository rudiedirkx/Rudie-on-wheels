<?php

namespace row\database\adapter;

use row\database\adapter\PDO;

class PDOSQLite extends PDO {

	/* Reflection */
	public function _getTables() {
		$tables = $this->selectFieldsNumeric('sqlite_master', 'name', array('type' => 'table'));
		usort($tables, 'strcasecmp');
		return $tables;
	}

	public function _getTableColumns( $table ) {
		$_columns = $this->fetch('pragma table_info('.$this->escapeAndQuoteTable($table).')');
		$columns = array();
		foreach ( $_columns AS $c ) {
			$c['type'] = strtoupper($c['type']);
			$c['null'] = !$c['notnull'];
			$c['default'] = $c['dflt_value'];
			$columns[$c['name']] = $c;
		}
		return $columns;
	}

	public function _getPKColumns( $table ) {
		$columns = $this->_getTableColumns($table);
		$columns = array_filter($columns, function($c) {
			return (bool)$c['pk'];
		});
		$columns = array_keys($columns);
		return $columns;
	}


	static public function initializable() {
		return in_array('pdo_sqlite', get_loaded_extensions());
	}


	public function connect() {
		$connection = $this->connectionArgs;
		$this->db = new \PDO('sqlite:'.$connection->path);
		$this->_fire('post_connect');
	}

	public function connected() {
		return is_object($this->query('SELECT 1 FROM sqlite_master'));
	}

	public function _post_connect() {
		if ( $this->connected() ) {
			$this->db->sqliteCreateFunction('IF', array('row\database\adapter\SQLite', 'fn_if'));
			$this->db->sqliteCreateFunction('RAND', array('row\database\adapter\SQLite', 'fn_rand'));
		}
	}

	public function escapeValue( $value ) {
		return str_replace("'", "''", (string)$value);
	}

	public function escapeTable( $table ) {
		return '"'.$table.'"';
	}

}


