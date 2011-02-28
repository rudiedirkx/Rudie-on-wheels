<?php

namespace row\database\adapter;

use row\database\SQLAdapter;
use row\database\DatabaseException;
use row\database\adapter\PDOpgSQL;

class pgSQL extends SQLAdapter {

	/* Reflection *
	public function _getTables() {
		$tables = $this->fetch('SHOW TABLES');
		$tables = array_map(function($r) {
			return reset($r);
		}, $tables);
		return $tables;
	}

	public function _getTableColumns( $table ) {
		$_columns = $this->fetch('EXPLAIN '.$table);
		$columns = array();
		foreach ( $_columns AS $c ) {
			$columns[$c['Field']] = $c;
		}
		return $columns;
	}

	public function _getPKColumns( $table ) {
		$columns = $this->_getTableColumns($table);
		$columns = array_filter($columns, function($c) {
			return 'PRI' == $c['Key'];
		});
		$columns = array_keys($columns);
		return $columns;
	}
	/**/


	static public function initializable() {
		return function_exists('pg_connect');
	}

	static public function open( $info, $do = true ) {
		if ( self::initializable() ) {
			return new self($info, $do);
		}
		return new PDOpgSQL($info, $do);
	}

	public function connect() {
		$connection = (array)$this->connectionArgs;
		$str = '';
		foreach ( $connection as $k => $v ) {
			$str .= ' '.$k.'='.$v;
		}
		$this->db = pg_connect(substr($str, 1));
		$this->_fire('post_connect');
	}

	public function connected() {
		return is_resource($this->db) && false !== $this->query('SELECT 1');
	}


	public function selectOne( $table, $field, $conditions, $params = array() ) {
		$conditions = $this->replaceholders($conditions, $params);
		$query = 'SELECT '.$field.' FROM '.$this->escapeAndQuoteTable($table).' WHERE '.$conditions;
		$r = $this->query($query);
		if ( !$r || 0 >= pg_num_rows($r) ) {
			return false;
		}
		return pg_fetch_result($r, 0, 0);
	}

	public function countRows( $query ) {
		$r = $this->query($query);
		if ( $r ) {
			return pg_num_rows($r);
		}
		return false;
	}

	public function fetchByField( $query, $field ) {
		$r = $this->query($query);
		if ( !$r ) {
			return false;
		}
		$a = array();
		while ( $l = pg_fetch_assoc($r) ) {
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
		while ( $l = pg_fetch_row($r) ) {
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
		while ( $l = pg_fetch_row($r) ) {
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
				return pg_fetch_object($r, null, $class);
			}
			return pg_fetch_assoc($r);
		}
		$a = array();
		if ( $class ) {
			while ( $l = pg_fetch_object($r, null, $class) ) {
				$a[] = $l;
			}
		}
		else {
			while ( $l = pg_fetch_assoc($r) ) {
				$a[] = $l;
			}
		}
		return $a;
	}

	public function query( $query ) {
		$q = pg_query($query, $this->db);
		if ( !$q ) {
			return $this->except($query.' -> '.$this->error());
		}
		return $q;
	}

	public function execute( $query ) {
		return $this->query($query);
	}

	public function error() {
		return pg_last_error($this->db);
	}

	public function errno() {
		return (int)(bool)pg_last_error($this->db);
	}

	public function affectedRows() {
		return pg_affected_rows($this->db);
	}

	public function insertId() {
		return pg_last_oid($this->db);
	}

	public function escapeValue( $value ) {
		return pg_escape_string((string)$value, $this->db);
	}

}


