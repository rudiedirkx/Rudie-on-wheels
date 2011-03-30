<?php

namespace row\database\adapter;

use row\database\Adapter;
use row\database\DatabaseException;
use row\database\adapter\PDOSQLite;
use row\database\adapter\SQLite3;

class SQLite extends Adapter {

	/* Reflection */
	public function _getTables() {
		$tables = $this->selectFieldsNumeric('sqlite_master', 'name', array('type' => 'table'));
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
			$db = new PDOSQLite($info, true);
			if ( $db->connected() ) {
				return $db;
			}
		}
/*		if ( SQLite3::initializable() ) {
			$db = new SQLite3($info, true);
			if ( $db->connected() ) {
				return $db;
			}
		}*/
		if ( self::initializable() ) {
			$db = new self($info, true);
			if ( $db->connected() ) {
				return $db;
			}
		}
	}

	public function connect() {
		$connection = $this->connectionArgs;
		$this->db = new \SQLiteDatabase($connection->path, $connection->mode ?: 0777);
		$this->_fire('post_connect');
	}

	public function connected() {
		return is_object($this->query('SELECT 1 FROM sqlite_master'));
	}

	public function _post_connect() {
		if ( $this->connected() ) {
			$this->db->createFunction('IF', array('row\database\adapter\SQLite', 'fn_if'));
			$this->db->createFunction('RAND', array('row\database\adapter\SQLite', 'fn_rand'));
		}
	}


	public function selectOne( $table, $field, $conditions, $params = array() ) {
		$conditions = $this->replaceholders($conditions, $params);
		$query = 'SELECT '.$field.' FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions;
		$r = $this->query($query);
		if ( !$r || 0 >= $r->numRows() ) {
			return false;
		}
		return $r->fetchSingle();
	}

	public function countRows( $query ) {
		$r = $this->query($query);
		if ( $r ) {
			return $r->numRows();
		}
		return false;
	}

	public function fetchByField( $query, $field ) {
		$r = $this->query($query);
		if ( !$r ) {
			return false;
		}
		$a = array();
		while ( $l = $r->fetch(SQLITE_ASSOC) ) {
			$a[$l[$field]] = $l;
		}
		return $a;
	}

	public function fetchFieldsAssoc( $query ) {
		$r = $this->query($query);
		if ( !$r ) {
			return false;
		}
		$a = array();
		while ( $l = $r->fetch(SQLITE_NUM) ) {
			$a[$l[0]] = $l[1];
		}
		return $a;
	}

	public function fetchFieldsNumeric( $query ) {
		$r = $this->query($query);
		if ( !$r ) {
			return false;
		}
		$a = array();
		while ( $l = $r->fetch(SQLITE_NUM) ) {
			$a[] = $l[0];
		}
		return $a;
	}

	public function fetch( $query, $class = null, $justFirst = false ) {
		$r = $this->query($query);
		if ( !$r ) {
			return false;
		}
		if ( !is_string($class) || !class_exists($class) ) {
			$class = false;
		}
		if ( $justFirst ) {
			if ( $class ) {
				return $r->fetchObject($class, array(true));
			}
			return $r->fetch(SQLITE_ASSOC);
		}
		$a = array();
		if ( $class ) {
			while ( $l = $r->fetchObject($class, array(true)) ) {
				$a[] = $l;
			}
		}
		else {
			while ( $l = $r->fetch(SQLITE_ASSOC) ) {
				$a[] = $l;
			}
		}
		return $a;
	}

	public function query( $query ) {
		$q = @$this->db->query($query);
		if ( !$q ) {
			return $this->except($query.' -> '.$this->error());
		}
		return $q;
	}

	public function execute( $query ) {
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


