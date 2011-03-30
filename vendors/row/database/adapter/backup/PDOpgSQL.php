<?php

namespace row\database\adapter;

use row\database\adapter\PDO;

class PDOpgSQL extends PDO {

	/* Reflection *
	public function _getTables() {
		$tables = $this->selectFieldsNumeric('sqlite_master', 'name', array('type' => 'table'));
		return $tables;
	}

	public function _getTableColumns( $table ) {
		$_columns = $this->fetch('pragma table_info('.$this->escapeAndQuoteTable($table).')');
		$columns = array();
		foreach ( $_columns AS $c ) {
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
	/**/


	static public function initializable() {
		return in_array('pdo_pgsql', get_loaded_extensions());
	}


	public function connect() {
		// ?
		$this->_fire('post_connect');
	}

	public function connected() {
		return false !== $this->query('SELECT 1');
	}

}


