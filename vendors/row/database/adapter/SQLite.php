<?php

namespace row\database\adapter;

use row\database\Adapter;
use row\database\DatabaseException;
use row\database\adapter\PDOSQLite;
use \SQLiteDatabase;
use \SQLiteException;

class SQLite extends Adapter {

	static public $events;

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


	static public function fn_if( $f_bool, $f_yes, $f_no ) {
		return $f_bool ? $f_yes : $f_no;
	}

	static public function fn_rand() {
		return rand(0, 99999999);
	}


	static public function initializable() {
		return class_exists('\SQLiteDatabase');
	}

	static public function open( $info, $do = true ) {

		if ( PDOSQLite::initializable() ) {
			$db = new PDOSQLite($info, $do);
			if ( $db->connected() ) {
				return $db;
			}
		}
		if ( self::initializable() ) {
			$db = new self($info, $do);
			if ( $db->connected() ) {
				return $db;
			}
		}
	}

	public function connect() {
		$connection = $this->connectionArgs;
		try {
			$this->db = @new SQLiteDatabase($connection->path, $connection->mode ?: 0777);
			$this->_fire('post_connect');
		}
		catch ( SQLiteException $ex ) {}
	}

	public function connected() {
		return $this->db && is_object($this->query('SELECT 1 FROM sqlite_master'));
	}

	public function _post_connect() {
		if ( $this->connected() ) {
			$this->db->createFunction('IF', array('row\database\adapter\SQLite', 'fn_if'));
			$this->db->createFunction('RAND', array('row\database\adapter\SQLite', 'fn_rand'));
		}
	}


	public function query( $query ) {
		$this->queries[] = $query;

		$q = @$this->db->query($query);
		if ( !$q ) {
			return $this->except($query.' -> '.$this->error());
		}

		return $q;
	}

	public function execute( $query ) {
		$this->queries[] = $query;

		$q = @$this->db->queryExec($query);
		if ( !$q ) {
			return $this->except($query.' -> '.$this->error());
		}

		return $q;
	}

	public function error() {
		return sqlite_error_string($this->errno());
	}

	public function errno() {
		return $this->db->lastError();
	}

	public function affectedRows() {
		return $this->dbCon->changes();
	}

	public function insertId() {
		return $this->dbCon->lastInsertRowid();
	}

	public function escapeValue( $value ) {
		return sqlite_escape_string((string)$value);
	}

	public function escapeTable( $table ) {
		return '"'.$table.'"';
	}

}



class SQLiteResult extends \row\database\QueryResult {

	public function singleResult() {
		return $this->result->fetchSingle();
	}

	public function nextObject( $class = '\stdClass', $args = array() ) {
		return $this->result->fetchObject($class, $args);
	}

	public function nextAssocArray() {
		return $this->result->fetch(SQLITE_ASSOC);
	}

	public function nextNumericArray() {
		return $this->result->fetch(SQLITE_NUM);
	}

	public function count() {
		return $this->result->numRows();
	}

}


