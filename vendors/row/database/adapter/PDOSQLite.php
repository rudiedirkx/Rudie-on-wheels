<?php

namespace row\database\adapter;

use row\database\adapter\PDO;

class PDOSQLite extends PDO {

	/* Reflection */
	public function _getTables() {
		$tables = $this->selectFieldsNumeric('sqlite_master', 'name', array('type' => 'table', "name <> 'sqlite_sequence'"));
		usort($tables, 'strcasecmp');
		return $tables;
	}

	public function _getTableColumns( $table ) {
		$_types = array(
			'integer' => 'int',
			'varchar' => 'text',
		);

		$_columns = $this->fetch('pragma table_info('.$this->escapeAndQuoteTable($table).')');

		$columns = array();
		foreach ( $_columns AS $c ) {
			$c['type'] = strtolower(is_int($p=strpos($c['type'], '(')) ? substr($c['type'], 0, $p) : $c['type']);
			isset($_types[$c['type']]) && $c['type'] = $_types[$c['type']];
			$c['null'] = !$c['notnull'];
			$c['default'] = trim($c['dflt_value'], "'");
			$c['primary'] = (bool)$c['pk'];

			$name = $c['name'];
			unset($c['cid'], $c['name'], $c['pk'], $c['notnull'], $c['dflt_value']);

			$columns[$name] = $c;
		}

		return $columns;
	}

	public function _getPKColumns( $table ) {
		$columns = $this->_getTableColumns($table);
		$columns = array_filter($columns, function($c) {
			return $c['primary'];
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
			$this->db->sqliteCreateFunction('CONCAT', array('row\database\adapter\SQLite', 'fn_concat'));
			$this->db->sqliteCreateFunction('SHA1', array('row\database\adapter\SQLite', 'fn_sha1'));
		}
	}

	public function escapeValue( $value ) {
		return str_replace("'", "''", (string)$value);
	}

#	public function quoteColumn( $column ) {
#		return !is_int(strpos($column, ' ')) && !is_int(strpos($column, '.')) ? '"'.$column.'"' : $column;
#	}

#	public function quoteTable( $table ) {
#		return !is_int(strpos($table, ' ')) && !is_int(strpos($table, '.')) ? '"'.$table.'"' : $table;
#	}

}


