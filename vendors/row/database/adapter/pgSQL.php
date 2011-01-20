<?php

namespace row\database\adapter;

use row\database\Adapter;
use row\database\DatabaseException;
use row\database\adapter\PDOpgSQL;

class pgSQL extends Adapter {

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
		if ( !$r || 0 >= mysql_num_rows($r) ) {
			return false;
		}
		return mysql_result($r, 0);
	}

	public function countRows( $query ) {
		$r = $this->query($query);
		if ( $r ) {
			return mysql_num_rows($r);
		}
		return false;
	}

	public function fetchByField( $query, $field ) {
		$r = $this->query($query);
		if ( !$r ) {
			return false;
		}
		$a = array();
		while ( $l = mysql_fetch_assoc($r) ) {
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
		while ( $l = mysql_fetch_row($r) ) {
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
		while ( $l = mysql_fetch_row($r) ) {
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
				return mysql_fetch_object($r, $class);
			}
			return mysql_fetch_assoc($r);
		}
		$a = array();
		if ( $class ) {
			while ( $l = mysql_fetch_object($r, $class) ) {
				$a[] = $l;
			}
		}
		else {
			while ( $l = mysql_fetch_assoc($r) ) {
				$a[] = $l;
			}
		}
		return $a;
	}

	public function query( $query ) {
		$q = mysql_query($query, $this->db);
		if ( !$q ) {
			if ( $this->throwExceptions ) {
				throw new DatabaseException($query.' -> '.$this->error());
			}
			return false;
		}
		return $q;
	}

	public function execute( $query ) {
		return $this->query($query);
	}

	public function error() {
		return mysql_error($this->db);
	}

	public function errno() {
		return mysql_errno($this->db);
	}

	public function affectedRows() {
		return mysql_affected_rows($this->db);
	}

	public function insertId() {
		return mysql_insert_id($this->db);
	}

	public function escapeValue( $value ) {
		return mysql_real_escape_string((string)$value, $this->db);
	}

}


